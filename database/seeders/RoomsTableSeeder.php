<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Room;

class RoomsTableSeeder extends Seeder
{
    public function run(): void
    {
        Room::updateOrCreate(['room_number' => '101'], ['name' => 'Computer Lab']);
        Room::updateOrCreate(['room_number' => '102'], ['name' => 'Electronics Lab']);
    }
}
