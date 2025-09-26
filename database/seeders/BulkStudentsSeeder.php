<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{User, Card};
use Illuminate\Support\Facades\Hash;

class BulkStudentsSeeder extends Seeder
{
    public function run(): void
    {
        for ($i = 1; $i <= 30; $i++) {
            $email = "student{$i}@example.com";
            $student = User::updateOrCreate(
                ['email' => $email],
                [
                    'student_no' => sprintf('STU-%04d', $i),
                    'firstname'  => "Student{$i}",
                    'lastname'   => 'Test',
                    'password'   => Hash::make('password'),
                    'role'       => 'student',
                    'status'     => 'active',
                ]
            );

            // Issue a fake card UID for testing
            $uid = strtoupper(sprintf('UID%06X', $i));
            Card::updateOrCreate(
                ['uid' => $uid],
                ['user_id' => $student->id, 'is_active' => true]
            );
        }
    }
}
