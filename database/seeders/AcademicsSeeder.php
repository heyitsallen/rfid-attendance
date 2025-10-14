<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\YearLevel;
use App\Models\Semester;
use App\Models\SchoolYear;
use App\Models\AttendanceStatus;

class AcademicsSeeder extends Seeder
{
    public function run(): void
    {
        // College IT Year Levels
        foreach (['1st Year', '2nd Year', '3rd Year', '4th Year'] as $lvl) {
            YearLevel::firstOrCreate(['name' => $lvl]);
        }

        // Create School Year first
        $schoolYear = SchoolYear::firstOrCreate(
            ['name' => '2025-2026'],
            [
                'date_start' => '2025-06-01',
                'date_end'   => '2026-03-31',
            ]
        );

        // Attach Semesters to this school year
        foreach (['1st Semester', '2nd Semester'] as $sem) {
            Semester::firstOrCreate(
                ['name' => $sem, 'school_year_id' => $schoolYear->id]
            );
        }

                foreach (['Present', 'Late', 'Absent', 'Excused'] as $status) {
            AttendanceStatus::firstOrCreate(['name' => $status]);
        };



    }

}
