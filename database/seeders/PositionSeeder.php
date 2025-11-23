<?php

namespace Database\Seeders;

use App\Models\Position;
use App\Models\PositionType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PositionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $positions = [
            [
                'employee_id' => 1,
                'office_id' => 1,
                'district_id' => 1,
                'initial_salary' => 3500.00,
                'bonuses' => 250.00,
                'status' => 1,
            ],
            [
                'employee_id' => 2,
                'office_id' => 2,
                'district_id' => 1,
                'initial_salary' => 4200.00,
                'bonuses' => null,
                'status' => 1,
            ],
            [
                'employee_id' => 3,
                'office_id' => 1,
                'district_id' => 2,
                'initial_salary' => 3900.00,
                'bonuses' => 150.00,
                'status' => 1,
            ],
        ];

        Position::insert($positions);
    }
}
