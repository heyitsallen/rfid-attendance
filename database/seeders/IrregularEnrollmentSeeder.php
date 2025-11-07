<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\{
    User, Role, StudentProfile, Card,
    SchoolYear, Semester,
    StudentScheduleEnrollment, SectionSchedule
};

class IrregularEnrollmentSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure term context (align with ITClassDemoSeeder defaults)
        $sy = SchoolYear::firstOrCreate(
            ['name' => '2025-2026'],
            ['date_start' => '2025-06-01', 'date_end' => '2026-03-31']
        );

        $sem = Semester::firstOrCreate([
            'school_year_id' => $sy->id,
            'name' => '1st Semester',
        ]);

        // Find a schedule to attach the irregular student to
        // Prefer the demo subject/schedule if present; otherwise any existing schedule
        $schedule = SectionSchedule::query()
            ->whereHas('assignment.subject', function ($q) {
                $q->where('course_code', 'IT201');
            })
            ->first();

        if (!$schedule) {
            $schedule = SectionSchedule::first();
        }

        if (!$schedule) {
            // Nothing to link to; abort early to avoid seeding inconsistent data
            $this->command?->warn('IrregularEnrollmentSeeder: No section schedules found. Skipping irregular seeding.');
            return;
        }

        // Make an irregular student account
        $student = User::firstOrCreate(
            ['email' => 'student.irregular@example.com'],
            [
                'firstname' => 'Ivy',
                'lastname'  => 'Rivera',
                'password'  => Hash::make('password123'),
                'status'    => 'active',
            ]
        );

        // Ensure student role/profile
        $roleStudent = Role::firstOrCreate(['name' => 'student']);
        $student->roles()->syncWithoutDetaching([$roleStudent->id]);
        $student->studentProfile()->firstOrCreate([], [
            'student_no' => '2025-IRREG-0001',
        ]);

        // Create irregular enrollment bound to this exact schedule and term
        StudentScheduleEnrollment::firstOrCreate([
            'student_id'          => $student->id,
            'section_schedule_id' => $schedule->id,
            'school_year_id'      => $sy->id,
            'semester_id'         => $sem->id,
        ], [
            'reason' => 'IRREG',
            'notes'  => 'Seeded irregular enrollment for demo/testing.',
        ]);

        // Optional: issue an active RFID card to the irregular student for quick testing
        Card::firstOrCreate(
            ['uid' => '460695AA', 'school_year_id' => $sy->id],
            [
                'user_id'   => $student->id,
                'is_active' => true,
                'issued_at' => now(),
                'notes'     => 'Demo irregular student card',
            ]
        );

        $this->command?->info('IrregularEnrollmentSeeder: Irregular student seeded and linked to schedule ID '.$schedule->id);
    }
}

