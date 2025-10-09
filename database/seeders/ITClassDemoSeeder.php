<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\CarbonPeriod;
use Carbon\Carbon;
use App\Models\{
    User, Role, StudentProfile, FacultyProfile,
    YearLevel, Section, SchoolYear, Semester,
    SubjectCurriculum, FacultyAssignedSubject, SectionSchedule,
    SectionEnrollment, Room, Device, StudentAttendance, Card
};

class ITClassDemoSeeder extends Seeder
{
    public function run(): void
    {
        // --- Term context (SY/Sem) ---
        $sy = SchoolYear::firstOrCreate(
            ['name' => '2025-2026'],
            ['date_start' => '2025-06-01', 'date_end' => '2026-03-31']
        );

        $sem = Semester::firstOrCreate([
            'school_year_id' => $sy->id,
            'name' => '1st Semester',
        ]);

        // --- Year Level & Section ---
        $yl = YearLevel::firstOrCreate(['name' => '2nd Year']);
        $section = Section::firstOrCreate(
            ['name' => 'BSIT 2A', 'year_level_id' => $yl->id]
        );

        // --- Room 101 & device ---
        $room = Room::where('room_number', '101')->firstOrFail();
        $device = Device::where('serial_no', 'room-101')->first(); // optional for attendance rows

        // --- Roles ---
        $roleStudent = Role::firstOrCreate(['name' => 'student']);
        $roleFaculty = Role::firstOrCreate(['name' => 'faculty']);

        // --- Faculty user ---
        $faculty = User::firstOrCreate(
            ['email' => 'faculty.demo@example.com'],
            [
                'firstname' => 'Alex',
                'lastname'  => 'Reyes',
                'password'  => Hash::make('password123'),
                'status'    => 'active',
            ]
        );
        $faculty->roles()->syncWithoutDetaching([$roleFaculty->id]);
        $faculty->facultyProfile()->firstOrCreate([], ['employee_no' => 'EMP-2025-0001']);

        // --- Student user ---
        $student = User::firstOrCreate(
            ['email' => 'student.demo@example.com'],
            [
                'firstname' => 'Casey',
                'lastname'  => 'Santos',
                'password'  => Hash::make('password123'),
                'status'    => 'active',
            ]
        );
        $student->roles()->syncWithoutDetaching([$roleStudent->id]);
        $student->studentProfile()->firstOrCreate([], ['student_no' => '2025-0001']);

        // --- Subject ---
        $subject = SubjectCurriculum::firstOrCreate(
            ['course_code' => 'IT201'],
            [
                'description' => 'Data Structures',
                'unit' => 3, 'lecture' => 3, 'laboratory' => 0,
            ]
        );

        // --- Assignment: faculty teaches this subject this term ---
        $assignment = FacultyAssignedSubject::firstOrCreate([
            'faculty_id'             => $faculty->id,
            'subject_curriculum_id'  => $subject->id,
            'school_year_id'         => $sy->id,
            'semester_id'            => $sem->id,
        ]);

        // --- Mon–Fri 2:00–4:00 PM schedule in Room 101 ---
        foreach (['Mon','Tue','Wed','Thu','Fri'] as $d) {
            SectionSchedule::firstOrCreate([
                'section_id'                  => $section->id,
                'faculty_assigned_subject_id' => $assignment->id,
                'room_id'                     => $room->id,
                'day'                         => $d,
                'start_time'                  => '14:00:00',
                'end_time'                    => '16:00:00',
            ]);
        }

        // --- Enroll student to the section for the term ---
        SectionEnrollment::firstOrCreate([
            'student_id'     => $student->id,
            'section_id'     => $section->id,
            'school_year_id' => $sy->id,
            'semester_id'    => $sem->id,
        ], ['date_enrolled' => now()]);

        // --- Seed recent attendance for the student (present) ---
        $today  = Carbon::now('Asia/Manila')->startOfDay();
        $start  = $today->copy()->subDays(14);
        $period = CarbonPeriod::create($start, $today);

        $schedules = SectionSchedule::where('section_id', $section->id)
            ->where('faculty_assigned_subject_id', $assignment->id)
            ->where('room_id', $room->id)
            ->get()
            ->keyBy('day'); // Mon..Fri

        $added = 0;
        foreach ($period as $date) {
            if ($added >= 7) break;
            $dayEnum = ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'][$date->isoWeekday()-1];
            if (!$schedules->has($dayEnum)) continue;

            if ($date->lt(Carbon::parse($sy->date_start)) || $date->gt(Carbon::parse($sy->date_end))) {
                continue;
            }

            $schedule  = $schedules[$dayEnum];
            $classDate = $date->toDateString();

            StudentAttendance::firstOrCreate([
                'student_id'          => $student->id,
                'section_schedule_id' => $schedule->id,
                'class_date'          => $classDate,
            ], [
                'device_id' => optional($device)->id,
                'time_in'   => Carbon::parse($classDate.' 14:05:00', 'Asia/Manila'),
                'time_out'  => Carbon::parse($classDate.' 16:00:00', 'Asia/Manila'),
                'status'    => 'present',
            ]);

            $added++;
        }

        // --- RFID Cards (UIDs) ---
        // Replace these placeholders with actual UIDs from your RC522 scanner.
        $studentUid = 'CEBA4D05'; // <-- change me
        $facultyUid = 'F2D20B01'; // <-- change me

        Card::firstOrCreate(
            ['uid' => $studentUid, 'school_year_id' => $sy->id],
            [
                'user_id'   => $student->id,
                'is_active' => true,
                'issued_at' => now(),
                'notes'     => 'Demo student card',
            ]
        );

        Card::firstOrCreate(
            ['uid' => $facultyUid, 'school_year_id' => $sy->id],
            [
                'user_id'   => $faculty->id,
                'is_active' => true,
                'issued_at' => now(),
                'notes'     => 'Demo faculty card',
            ]
        );
    }
}
