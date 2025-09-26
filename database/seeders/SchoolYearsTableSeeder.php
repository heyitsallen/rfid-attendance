<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SchoolYear;

class SchoolYearsTableSeeder extends Seeder
{
    public function run(): void
    {
        SchoolYear::updateOrCreate(
            ['name' => '2025-2026'],
            ['date_start' => '2025-08-01', 'date_end' => '2026-05-31']
        );
    }
}
