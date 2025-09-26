<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\{Device, Room};

class DevicesTableSeeder extends Seeder
{
    public function run(): void
    {
        $room101 = Room::where('room_number', '101')->first();

        Device::updateOrCreate(
            ['serial_no' => 'room-101'],
            [
                'token'       => env('DEVICE_ROOM_101_TOKEN', 'VpAclEfle3OEFET1FPybWDILVxnpQtpaptqp6fiContWSBT2zTuooKgnt4rA30Ky' /*Str::random(64)*/),
                'room_id'     => $room101?->id,
                'description' => 'Main NodeMCU for Room 101',
            ]
        );
    }
}
