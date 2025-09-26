<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UsersTableSeeder extends Seeder
{
    public function run(): void
    {
        // Admin
        User::updateOrCreate(
            ['email' => 'yesitsmeallen@gmail.com'],
            [
                'firstname' => 'Edward Allen',
                'lastname'  => 'Chua',
                'password'  => Hash::make('password123'),
                'role'      => 'admin',
                'status'    => 'active',
            ]
        );

        // Faculty
        User::updateOrCreate(
            ['email' => 'faculty@example.com'],
            [
                'employee_no' => 'EMP-1001',
                'firstname'   => 'Juan',
                'lastname'    => 'Dela Cruz',
                'password'    => Hash::make('password'),
                'role'        => 'faculty',
                'status'      => 'active',
            ]
        );

        // Student
        User::updateOrCreate(
            ['email' => 'student@example.com'],
            [
                'student_no' => 'STU-2001',
                'firstname'  => 'Maria',
                'lastname'   => 'Santos',
                'password'   => Hash::make('password'),
                'role'       => 'student',
                'status'     => 'active',
            ]
        );
    }
}
