<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\EmployeeStatus;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $employees = [
            [
                'full_name' => 'John Doe',
                'dpi' => '1234567890101',
                'birth_date' => '1990-04-12',
                'phone' => '55581234',
                'email' => 'john@example.com',
                'files' => null,
                'status_id' => EmployeeStatus::inRandomOrder()->first()->id,
            ],
            [
                'full_name' => 'Jane Smith',
                'dpi' => '9876543210101',
                'birth_date' => '1995-09-22',
                'phone' => '55591234',
                'email' => 'jane@example.com',
                'files' => null,
                'status_id' => EmployeeStatus::inRandomOrder()->first()->id,
            ],
            [
                'full_name' => 'Carlos Ramirez',
                'dpi' => '2020202020202',
                'birth_date' => '1988-12-05',
                'phone' => '55671234',
                'email' => 'carlos@example.com',
                'files' => null,
                'status_id' => EmployeeStatus::inRandomOrder()->first()->id,
            ],
        ];

        Employee::insert($employees);
    }
}
