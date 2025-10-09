<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Room;
use App\Models\Device;
use Illuminate\Support\Str;

class RoomsAndDevicesSeeder extends Seeder
{
    public function run(): void
    {
        // Room 101 with RFID Device
        $room101 = Room::firstOrCreate(
            ['room_number' => '101'],
            ['name' => 'Room 101']
        );

        Device::firstOrCreate(
            ['serial_no' => 'room-101'],
            [
                'token'       => Str::random(64),
                'room_id'     => $room101->id,
                'description' => 'RFID Device for Room 101',
            ]
        );

        // Room Admin with RFID Device (for enrollment station)
        $roomAdmin = Room::firstOrCreate(
            ['room_number' => 'ADMIN'],
            ['name' => 'Room Admin']
        );

        Device::firstOrCreate(
            ['serial_no' => 'room-admin'],
            [
                'token'       => Str::random(64),
                'room_id'     => $roomAdmin->id,
                'description' => 'RFID Device for Admin Enrollment',
            ]
        );
    }
}
