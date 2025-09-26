<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{
    Device, User, Card, SectionSchedule,
    StudentAttendance, FacultyAttendance
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

        // 2) Resolve card -> user
        $card = Card::where('uid', $data['uid'])->where('is_active', true)->first();
        if (!$card || !$card->user) {
            return response()->json([
                'ok' => false,
                'display_line1' => 'CARD NOT LINKED',
                'display_line2' => $data['uid'],
                'beep' => 'error'
            ], 404);
        }
        $user = $card->user;

        // 3) Find current schedule
        // NOTE: If you want device/room binding, add room_id to section_schedules and keep the room filter below.
        $scheduleQuery = SectionSchedule::query()
            ->where('day', $dayEnum)
            ->whereTime('start_time', '<=', $timeNow)
            ->whereTime('end_time', '>=', $timeNow);

        // Optional: filter by room if you add room_id to section_schedules
        // $scheduleQuery->where('room_id', $device->room_id);

        if ($user->role === 'faculty') {
            $scheduleQuery->where('faculty_id', $user->id);
        } elseif ($user->role === 'student') {
            // If you maintain enrollments, you can restrict schedules to the student's section here.
            // For now we don't filter by section due to no enrollments table.
        }

        $schedule = $scheduleQuery->first();

        if (!$schedule) {
            return response()->json([
                'ok' => false,
                'display_line1' => 'NO CLASS NOW',
                'display_line2' => 'Check schedule',
                'beep' => 'error'
            ], 200);
        }

        // 4) Handle attendance based on role
        if ($user->role === 'student') {
            return $this->handleStudent($user, $schedule, $device, $now, $classDate);
        }

        if ($user->role === 'faculty') {
            return $this->handleFaculty($user, $schedule, $device, $now, $classDate);
        }

        return response()->json([
            'ok' => false,
            'display_line1' => 'ROLE NOT SUPPORTED',
            'display_line2' => $user->role,
            'beep' => 'error'
        ], 422);
    }

    private function handleStudent(User $user, SectionSchedule $schedule, Device $device, Carbon $now, string $classDate)
    {
        // Retrieve or create today’s attendance record for this schedule & student
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

        // If no time_in yet → mark IN
        if (is_null($att->time_in)) {
            $att->time_in = $now;

            // Late rule: > 15 minutes after start
            $start = Carbon::createFromFormat('H:i:s', $schedule->start_time, 'Asia/Manila');
            $att->status = $now->greaterThan($start->copy()->addMinutes(15)) ? 'late' : 'present';
            $att->device_id = $device->id;
            $att->save();

            return response()->json([
                'ok' => true,
                'display_line1' => 'WELCOME',
                'display_line2' => $user->student_no ?? $user->getFullNameAttribute(),
                'beep' => 'ok'
            ], 200);
        }

        // If time_in exists but no time_out → mark OUT
        if (is_null($att->time_out)) {
            $att->time_out = $now;
            // keep status as is
            $att->device_id = $device->id;
            $att->save();

            return response()->json([
                'ok' => true,
                'display_line1' => 'GOODBYE',
                'display_line2' => $user->student_no ?? $user->getFullNameAttribute(),
                'beep' => 'double'
            ], 200);
        }

        // Otherwise, already completed IN/OUT
        return response()->json([
            'ok' => true,
            'display_line1' => 'ALREADY MARKED',
            'display_line2' => $user->student_no ?? $user->getFullNameAttribute(),
            'beep' => 'ok'
        ], 200);
    }

    private function handleFaculty(User $user, SectionSchedule $schedule, Device $device, Carbon $now, string $classDate)
    {
        // Ensure faculty matches schedule
        if ((int)$schedule->faculty_id !== (int)$user->id) {
            return response()->json([
                'ok' => false,
                'display_line1' => 'NOT AUTHORIZED',
                'display_line2' => $user->employee_no ?? $user->getFullNameAttribute(),
                'beep' => 'error'
            ], 200);
        }

        $att = FacultyAttendance::firstOrCreate(
            [
                'faculty_id'          => $user->id,
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
                'display_line2' => $user->employee_no ?? $user->getFullNameAttribute(),
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
                'display_line2' => $user->employee_no ?? $user->getFullNameAttribute(),
                'beep' => 'double'
            ], 200);
        }

        return response()->json([
            'ok' => true,
            'display_line1' => 'ALREADY MARKED',
            'display_line2' => $user->employee_no ?? $user->getFullNameAttribute(),
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
