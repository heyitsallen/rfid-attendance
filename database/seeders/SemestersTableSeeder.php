<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Semester;

class SemestersTableSeeder extends Seeder
{
    public function run(): void
    {
        Semester::updateOrCreate(['name' => '1st Semester'], []);
        Semester::updateOrCreate(['name' => '2nd Semester'], []);
    }
}
