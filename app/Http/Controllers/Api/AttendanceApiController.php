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
use Illuminate\Support\Facades\DB;

class AttendanceApiController extends Controller
{
    private int $LATE_MINUTES = 15;

    public function scan(Request $r)
    {
        // Minimal logging; do NOT leak secrets in logs in production
        Log::info('SCAN request', [
            'serial_no' => $r->input('serial_no'),
            'device'    => '***',
            'uid'       => $r->input('uid'),
            'ip'        => $r->ip(),
        ]);

        $data = $r->validate([
            'serial_no'    => 'required|string',
            'device_token' => 'required|string',
            'uid'          => 'required|string',
        ]);

        $now       = Carbon::now('Asia/Manila');
        $classDate = $now->toDateString();
        $timeNow   = $now->format('H:i:s');
        $dayEnum   = $this->dayToEnum($now);

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

        // 2) Resolve active card → user
        $card = Card::where('uid', $data['uid'])
            ->where('is_active', true)
            ->with('user.roles','user.studentProfile','user.facultyProfile')
            ->first();

        if (!$card || !$card->user) {
            return response()->json([
                'ok' => false,
                'display_line1' => 'CARD NOT LINKED',
                'display_line2' => $data['uid'],
                'beep' => 'error'
            ], 404);
        }

        $user      = $card->user;
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
            ->when($device->room_id, fn($q) => $q->where('room_id', $device->room_id))
            ->when($isFaculty, function ($q) use ($user) {
                $q->whereHas('assignment', fn($qa) => $qa->where('faculty_id', $user->id));
            })
            ->first();

        if (!$schedule) {
            return response()->json([
                'ok' => false,
                'display_line1' => 'NO CLASS NOW',
                'display_line2' => 'Check schedule',
                'beep' => 'error'
            ], 200);
        }

        // If student, validate enrollment against this schedule's term + section
        if ($isStudent) {
            $assignment = $schedule->assignment;
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

        // 4) Route by role
        if ($isFaculty) {
            return $this->handleFaculty($user, $schedule, $device, $now, $classDate);
        }
        if ($isStudent) {
            return $this->handleStudent($user, $schedule, $device, $now, $classDate);
        }

        return response()->json([
            'ok' => false,
            'display_line1' => 'ROLE NOT SUPPORTED',
            'display_line2' => $user->full_name,
            'beep' => 'error'
        ], 422);
    }

    private function handleStudent(User $user, SectionSchedule $schedule, Device $device, Carbon $now, string $classDate)
    {
        // Determine effective start based on FACULTY time_in for this class/day
        $facultyIn = FacultyAttendance::where('section_schedule_id', $schedule->id)
            ->where('class_date', $classDate)
            ->value('time_in'); // Carbon|string|null

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

        // Prepare display
        $fullName = $user->full_name;
        $subject  = $schedule->assignment?->subject?->course_code ?? '';
        $section  = $schedule->section?->name ?? '';
        $dateDisp = $now->format('M d, Y');

        // IN
        if (is_null($att->time_in)) {
            $att->time_in   = $now;
            $att->device_id = $device->id;

            // RULE: If faculty NOT yet IN → student is PRESENT regardless of time.
            if (empty($facultyIn)) {
                $att->status = 'present';
            } else {
                // Grace starts from facultyIn
                $facultyStart = Carbon::parse($facultyIn, 'Asia/Manila');
                $isLate = $now->greaterThan($facultyStart->copy()->addMinutes($this->LATE_MINUTES));
                $att->status = $isLate ? 'late' : 'present';
            }

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

        // OUT
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

        // Already completed
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
        $assignment = $schedule->assignment;
        if (!$assignment || (int)$assignment->faculty_id !== (int)$user->id) {
            return response()->json([
                'ok' => false,
                'display_line1' => 'NOT AUTHORIZED',
                'display_line2' => $user->facultyProfile?->employee_no ?? $user->full_name,
                'beep' => 'error'
            ], 200);
        }

        return DB::transaction(function () use ($user, $schedule, $device, $now, $classDate, $assignment) {
            $att = FacultyAttendance::firstOrCreate(
                [
                    'section_schedule_id' => $schedule->id,
                    'class_date'          => $classDate,
                ],
                [
                    'device_id' => $device->id,
                ]
            );

            // FACULTY IN → defines class start & begins grace for students
            if (is_null($att->time_in)) {
                $att->time_in   = $now;
                $att->status    = 'in';
                $att->device_id = $device->id;
                $att->save();

                return response()->json([
                    'ok' => true,
                    'display_line1' => 'FACULTY IN',
                    'display_line2' => $user->facultyProfile?->employee_no ?? $user->full_name,
                    'display_line3' => 'Grace started',
                    'beep' => 'ok'
                ], 200);
            }

            // FACULTY OUT → auto student OUT + auto ABSENT for non-tappers
            if (is_null($att->time_out)) {
                $att->time_out  = $now;
                $att->status    = 'out';
                $att->device_id = $device->id;
                $att->save();

                // 1) AUTO-TIMEOUT students who tapped IN but not OUT
                $autoOutAt = $now;
                $autoOutCount = StudentAttendance::where('section_schedule_id', $schedule->id)
                    ->where('class_date', $classDate)
                    ->whereNotNull('time_in')
                    ->whereNull('time_out')
                    ->update([
                        'time_out'   => $autoOutAt,
                        'updated_at' => now(),
                    ]);

                // 2) AUTO-ABSENT all enrolled students who never tapped (no attendance row)
                // Get enrolled student IDs for this section & term
                $enrolledIds = SectionEnrollment::where('section_id', $schedule->section_id)
                    ->where('school_year_id', $assignment->school_year_id)
                    ->where('semester_id', $assignment->semester_id)
                    ->pluck('student_id')
                    ->unique()
                    ->values();

                if ($enrolledIds->isNotEmpty()) {
                    // Find who already has an attendance row (any)
                    $already = StudentAttendance::where('section_schedule_id', $schedule->id)
                        ->where('class_date', $classDate)
                        ->whereIn('student_id', $enrolledIds)
                        ->pluck('student_id')
                        ->unique();

                    $missing = $enrolledIds->diff($already);

                    // Insert ABSENT rows for missing
                    if ($missing->isNotEmpty()) {
                        $nowTs = now();
                        $rows = $missing->map(function ($sid) use ($schedule, $classDate, $device, $nowTs) {
                            return [
                                'student_id'          => $sid,
                                'section_schedule_id' => $schedule->id,
                                'class_date'          => $classDate,
                                'status'              => 'absent',
                                'device_id'           => $device->id,   // marks device that closed class
                                'created_at'          => $nowTs,
                                'updated_at'          => $nowTs,
                            ];
                        })->all();

                        // Bulk insert
                        StudentAttendance::insert($rows);
                    }
                }

                $absentCount = isset($rows) ? count($rows) : 0;

                return response()->json([
                    'ok' => true,
                    'display_line1' => 'FACULTY OUT',
                    'display_line2' => $user->facultyProfile?->employee_no ?? $user->full_name,
                    'display_line3' => "Auto-out: {$autoOutCount}  Absent: {$absentCount}",
                    'beep' => 'double'
                ], 200);
            }

            // Already completed
            return response()->json([
                'ok' => true,
                'display_line1' => 'ALREADY MARKED',
                'display_line2' => $user->facultyProfile?->employee_no ?? $user->full_name,
                'beep' => 'ok'
            ], 200);
        });
    }

    private function dayToEnum(Carbon $now): string
    {
        // isoWeekday: 1=Mon ... 7=Sun
        $map = [1=>'Mon',2=>'Tue',3=>'Wed',4=>'Thu',5=>'Fri',6=>'Sat',7=>'Sun'];
        return $map[$now->isoWeekday()];
    }
}
