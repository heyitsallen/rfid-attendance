<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{FacultyAssignedSubject, User, SubjectCurriculum, SchoolYear, Semester};

class FacultyAssignedSubjectsTableSeeder extends Seeder
{
    public function run(): void
    {
        $faculty  = User::where('role','faculty')->first();
        $subject  = SubjectCurriculum::where('course_code','CS101')->first();
        $sy       = SchoolYear::where('name','2025-2026')->first();
        $semester = Semester::where('name','1st Semester')->first();

        if ($faculty && $subject && $sy && $semester) {
            FacultyAssignedSubject::updateOrCreate(
                [
                    'faculty_id'            => $faculty->id,
                    'subject_curriculum_id' => $subject->id,
                    'school_year_id'        => $sy->id,
                    'semester_id'           => $semester->id,
                ],
                []
            );
        }
    }
}
