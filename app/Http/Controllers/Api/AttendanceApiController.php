<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{
    Device, User, Card, SectionSchedule,
    StudentAttendance, FacultyAttendance, SectionEnrollment
};
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AttendanceApiController extends Controller
{
    public function scan(Request $r)
    {
        Log::info('SCAN request', $r->all());

        $data = $r->validate([
            'serial_no'    => 'required|string',
            'device_token' => 'required|string',
            'uid'          => 'required|string', // RFID card UID
        ]);

        $now        = Carbon::now('Asia/Manila');
        $classDate  = $now->toDateString();
        $timeNow    = $now->format('H:i:s');
        $dayEnum    = $this->dayToEnum($now); // "Mon".."Sun"

        // 1) Verify device
        $device = Device::where('serial_no', $data['serial_no'])
            ->where('token', $data['device_token'])
            ->first();

        if (!$device) {
            return response()->json([
                'ok' => false,
                'display_line1' => 'INVALID DEVICE',
                'display_line2' => 'Check token/serial',
                'beep' => 'error'
            ], 401);
        }

        // 2) Resolve card -> user (only active cards)
        $card = Card::where('uid', $data['uid'])->where('is_active', true)->with('user')->first();
        if (!$card || !$card->user) {
            return response()->json([
                'ok' => false,
                'display_line1' => 'CARD NOT LINKED',
                'display_line2' => $data['uid'],
                'beep' => 'error'
            ], 404);
        }
        $user = $card->user;

        $isStudent = $user->hasRole('student');
        $isFaculty = $user->hasRole('faculty');

        if (!$isStudent && !$isFaculty) {
            return response()->json([
                'ok' => false,
                'display_line1' => 'ROLE NOT ALLOWED',
                'display_line2' => $user->full_name,
                'beep' => 'error'
            ], 422);
        }

        // 3) Find a current schedule window (by day/time and optional room)
        $schedule = SectionSchedule::with(['assignment.faculty','assignment.subject','section','room'])
            ->where('day', $dayEnum)
            ->whereTime('start_time', '<=', $timeNow)
            ->whereTime('end_time', '>=', $timeNow)
            // Bind to device room if set
            ->when($device->room_id, fn($q) => $q->where('room_id', $device->room_id))
            // If card user is faculty, only schedules taught by them
            ->when($isFaculty, function ($q) use ($user) {
                $q->whereHas('assignment', fn($qa) => $qa->where('faculty_id', $user->id));
            })
            // If card user is student, we don't filter here by enrollment (we’ll validate after we have a schedule)
            ->first();

        if (!$schedule) {
            return response()->json([
                'ok' => false,
                'display_line1' => 'NO CLASS NOW',
                'display_line2' => 'Check schedule',
                'beep' => 'error'
            ], 200);
        }

        // 3b) If student, validate enrollment for the schedule’s term (SY/Sem) and section
        if ($isStudent) {
            $assignment = $schedule->assignment; // FacultyAssignedSubject
            if (!$assignment) {
                return response()->json([
                    'ok' => false,
                    'display_line1' => 'SCHED ERROR',
                    'display_line2' => 'No assignment',
                    'beep' => 'error'
                ], 200);
            }

            $enrolled = SectionEnrollment::where('student_id', $user->id)
                ->where('section_id', $schedule->section_id)
                ->where('school_year_id', $assignment->school_year_id)
                ->where('semester_id', $assignment->semester_id)
                ->exists();

            if (!$enrolled) {
                return response()->json([
                    'ok' => false,
                    'display_line1' => 'NOT ENROLLED',
                    'display_line2' => $user->studentProfile?->student_no ?? $user->full_name,
                    'beep' => 'error'
                ], 200);
            }
        }

        // 4) Handle attendance based on active role
        if ($isStudent) {
            return $this->handleStudent($user, $schedule, $device, $now, $classDate);
        }

        if ($isFaculty) {
            return $this->handleFaculty($user, $schedule, $device, $now, $classDate);
        }

        // Fallback (shouldn’t reach)
        return response()->json([
            'ok' => false,
            'display_line1' => 'ROLE NOT SUPPORTED',
            'display_line2' => $user->full_name,
            'beep' => 'error'
        ], 422);
    }

private function handleStudent(User $user, SectionSchedule $schedule, Device $device, Carbon $now, string $classDate)
{
    $att = StudentAttendance::firstOrCreate(
        [
            'student_id'          => $user->id,
            'section_schedule_id' => $schedule->id,
            'class_date'          => $classDate,
        ],
        [
            'device_id' => $device->id,
        ]
    );

    // Safely parse start time
    $rawStart = $schedule->getRawOriginal('start_time') ?? '00:00:00';
    $start    = Carbon::parse($classDate.' '.$rawStart, 'Asia/Manila');

    // Resolve extra display info
    $fullName = $user->full_name; // accessor you already have
    $subject  = $schedule->assignment?->subject?->course_code ?? '';
    $section  = $schedule->section?->name ?? '';
    $dateDisp = $now->format('M d, Y');

    // If no time_in yet → mark IN
    if (is_null($att->time_in)) {
        $att->time_in   = $now;
        $att->status    = $now->greaterThan($start->copy()->addMinutes(15)) ? 'late' : 'present';
        $att->device_id = $device->id;
        $att->save();

        return response()->json([
            'ok' => true,
            'display_line1' => "WELCOME",
            'display_line2' => $fullName,
            'display_line3' => "$subject - $section",
            'display_line4' => $dateDisp,
            'beep' => 'ok'
        ], 200);
    }

    // If time_in exists but no time_out → mark OUT
    if (is_null($att->time_out)) {
        $att->time_out  = $now;
        $att->device_id = $device->id;
        $att->save();

        return response()->json([
            'ok' => true,
            'display_line1' => "GOODBYE",
            'display_line2' => $fullName,
            'display_line3' => "$subject - $section",
            'display_line4' => $dateDisp,
            'beep' => 'double'
        ], 200);
    }

    // Otherwise, already completed IN/OUT
    return response()->json([
        'ok' => true,
        'display_line1' => "ALREADY MARKED",
        'display_line2' => $fullName,
        'display_line3' => "$subject - $section",
        'display_line4' => $dateDisp,
        'beep' => 'ok'
    ], 200);
}



    private function handleFaculty(User $user, SectionSchedule $schedule, Device $device, Carbon $now, string $classDate)
    {
        // Ensure faculty matches schedule (derived from assignment)
        $assignment = $schedule->assignment;
        if (!$assignment || (int)$assignment->faculty_id !== (int)$user->id) {
            return response()->json([
                'ok' => false,
                'display_line1' => 'NOT AUTHORIZED',
                'display_line2' => $user->facultyProfile?->employee_no ?? $user->full_name,
                'beep' => 'error'
            ], 200);
        }

        $att = FacultyAttendance::firstOrCreate(
            [
                'section_schedule_id' => $schedule->id,
                'class_date'          => $classDate,
            ],
            [
                'device_id' => $device->id,
            ]
        );

        if (is_null($att->time_in)) {
            $att->time_in = $now;
            $att->status  = 'in';
            $att->device_id = $device->id;
            $att->save();

            return response()->json([
                'ok' => true,
                'display_line1' => 'FACULTY IN',
                'display_line2' => $user->facultyProfile?->employee_no ?? $user->full_name,
                'beep' => 'ok'
            ], 200);
        }

        if (is_null($att->time_out)) {
            $att->time_out = $now;
            $att->status   = 'out';
            $att->device_id = $device->id;
            $att->save();

            return response()->json([
                'ok' => true,
                'display_line1' => 'FACULTY OUT',
                'display_line2' => $user->facultyProfile?->employee_no ?? $user->full_name,
                'beep' => 'double'
            ], 200);
        }

        return response()->json([
            'ok' => true,
            'display_line1' => 'ALREADY MARKED',
            'display_line2' => $user->facultyProfile?->employee_no ?? $user->full_name,
            'beep' => 'ok'
        ], 200);
    }

    private function dayToEnum(Carbon $now): string
    {
        // isoWeekday: 1=Mon ... 7=Sun
        $map = [1=>'Mon',2=>'Tue',3=>'Wed',4=>'Thu',5=>'Fri',6=>'Sat',7=>'Sun'];
        return $map[$now->isoWeekday()];
    }
}
