<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    public function run(): void
    {
        // Admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'firstname' => 'System',
                'lastname'  => 'Administrator',
                'password'  => Hash::make('password123'),
                'status'    => 'active',
            ]
        );

        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole && !$admin->roles()->where('role_id', $adminRole->id)->exists()) {
            $admin->roles()->attach($adminRole->id);
        }

        // Faculty
        $faculty = User::firstOrCreate(
            ['email' => 'faculty1@example.com'],
            [
                'firstname' => 'Jane',
                'lastname'  => 'Doe',
                'password'  => Hash::make('password123'),
                'status'    => 'active',
            ]
        );
        $faculty->roles()->syncWithoutDetaching([Role::where('name', 'faculty')->first()->id]);

        // Student
        $student = User::firstOrCreate(
            ['email' => 'student1@example.com'],
            [
                'firstname' => 'John',
                'lastname'  => 'Smith',
                'password'  => Hash::make('password123'),
                'status'    => 'active',
            ]
        );
        $student->roles()->syncWithoutDetaching([Role::where('name', 'student')->first()->id]);
    }
}
