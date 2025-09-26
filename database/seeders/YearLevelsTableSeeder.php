<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\YearLevel;

class YearLevelsTableSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['1st Year','2nd Year','3rd Year','4th Year'] as $name) {
            YearLevel::updateOrCreate(['name' => $name], []);
        }
    }
}
