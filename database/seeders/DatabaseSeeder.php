<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ---- Base reference data ----
        $this->call([
            UsersTableSeeder::class,                // admin/faculty/student
            RoomsTableSeeder::class,                // rooms
            YearLevelsTableSeeder::class,           // 1st..4th year
            SectionsTableSeeder::class,             // e.g. BSCS-1A
            SchoolYearsTableSeeder::class,          // e.g. 2025-2026
            SemestersTableSeeder::class,            // 1st/2nd semester
            SubjectCurriculumsTableSeeder::class,   // e.g. CS101
        ]);

        // ---- Depends on rooms ----
        $this->call([
            DevicesTableSeeder::class,              // uses rooms
        ]);

        // ---- Depends on users + subject + school year (+ semester) ----
        $this->call([
            FacultyAssignedSubjectsTableSeeder::class, // links faculty + subject + SY (+ semester)
        ]);

        // ---- Depends on section + faculty assignment + room ----
        $this->call([
            SectionSchedulesTableSeeder::class,     // creates actual class slot
        ]);

        // ---- Optional: link real card UIDs to users (for quick testing) ----
        $this->call([
            CardsTableSeeder::class,                // replace UIDs with real ones or skip
        ]);

        // ---- Optional bulk gen (uncomment if you want to load many students) ----
        // $this->call([
        //     BulkStudentsSeeder::class,           // creates N students + cards (fake UIDs)
        // ]);
    }
}
