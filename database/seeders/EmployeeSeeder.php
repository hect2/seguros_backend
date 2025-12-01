<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\EmployeeStatus;
use App\Models\Office;
use App\Models\Position;
use App\Models\PositionType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 0; $i < 20; $i++) {
            $digessp = fake()->date();
            Employee::create([
                'full_name' => fake()->name(),
                'dpi' => fake()->unique()->numerify('#############'), // 13 dígitos
                'birth_date' => fake()->date(),
                'phone' => fake()->numerify('########'), // 8 dígitos GT
                'email' => fake()->unique()->safeEmail(),
                'files' => null,
                'status_id' => EmployeeStatus::inRandomOrder()->first()->id,
                'digessp_fecha_vencimiento'=> fake()->randomElement([null, $digessp]),
            ]);
        }

        $employments = Employee::all();
        foreach ($employments as $employment) {
            $office = Office::inRandomOrder()->first();
            $operative_id = PositionType::inRandomOrder()->first()->id;

            Position::create([
                'employee_id' => $employment->id,
                'office_id' => $office->id,
                'district_id' => $office->district_id,
                'admin_position_type_id' => PositionType::inRandomOrder()->first()->id,
                'operative_position_type_id' => fake()->randomElement([null, $operative_id]),
                'initial_salary' => fake()->numberBetween(3000, 5000),
                'bonuses' => fake()->numberBetween(0, 500),
                'status' => fake()->randomElement([1, 0]),
            ]);
        }
    }
}
