<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SubjectCurriculum;

class SubjectCurriculumsTableSeeder extends Seeder
{
    public function run(): void
    {
        SubjectCurriculum::updateOrCreate(
            ['course_code' => 'IT101'],
            ['description' => 'Introduction to Computing', 'unit' => 3, 'lecture' => 3, 'laboratory' => 0]
        );
    }
}
