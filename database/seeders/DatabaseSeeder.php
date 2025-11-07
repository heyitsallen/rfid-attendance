<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolesTableSeeder::class,
            UsersTableSeeder::class,
            AcademicsSeeder::class,
            RoomsAndDevicesSeeder::class,
            ITClassDemoSeeder::class,
            IrregularEnrollmentSeeder::class,
        ]);
    }
}
