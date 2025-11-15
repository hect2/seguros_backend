<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Migrations\StatusCommand;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\UserSeeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();


        $this->call([
            PermissionSeeder::class,
            RolePermissionSeeder::class,
            DistrictSeeder::class,
            UserSeeder::class,
            OfficeSeeder::class,
            TypeSeeder::class,
            CriticalSeeder::class,
            IncidentStatusSeeder::class,
            IncidentSeeder::class,
        ]);
    }
}
