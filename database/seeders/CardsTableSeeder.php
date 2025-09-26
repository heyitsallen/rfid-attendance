<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{Card, User};

class CardsTableSeeder extends Seeder
{
    public function run(): void
    {
        $faculty = User::where('role','faculty')->first();
        $student = User::where('role','student')->first();

        // Replace UIDs with your real tags (uppercase hex, no spaces)
        if ($faculty) {
            Card::updateOrCreate(
                ['uid' => 'F2D20B01'],
                ['user_id' => $faculty->id, 'is_active' => true]
            );
        }

        if ($student) {
            Card::updateOrCreate(
                ['uid' => 'CEBA4D05'],
                ['user_id' => $student->id, 'is_active' => true]
            );
        }
    }
}
