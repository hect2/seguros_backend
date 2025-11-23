<?php

namespace Database\Seeders;

use App\Models\EmployeeStatus;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EmployeeStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $status = [
            [
                'name' => 'Activo',
                'slug' => 'active',
                'description' => 'Empleado actualmente activo',
            ],
            [
                'name' => 'En revisión',
                'slug' => 'under_review',
                'description' => 'Empleado en proceso de evaluación o revisión',
            ],
            [
                'name' => 'Pendiente',
                'slug' => 'pending',
                'description' => 'Empleado pendiente de aprobación o actividad',
            ],
            [
                'name' => 'Inactivo',
                'slug' => 'inactive',
                'description' => 'Empleado inactivo o dado de baja',
            ],
        ];

        foreach ($status as $key => $value) {
            EmployeeStatus::updateOrCreate(
                ['slug' => $value['slug']],
                [
                    'name' => $value['name'],
                    'description' => $value['description'],
                ]
            );
        }
    }
}
