<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{Section, YearLevel};

class SectionsTableSeeder extends Seeder
{
    public function run(): void
    {
        $yl = YearLevel::where('name', '1st Year')->first();
        Section::updateOrCreate(['name' => 'BSCS-1A'], ['year_level_id' => $yl?->id]);
    }
}
