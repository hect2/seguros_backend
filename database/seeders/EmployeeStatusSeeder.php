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
                'name' => 'En revisión - Talento Humano',
                'slug' => 'under_review_th',
                'description' => 'Empleado en proceso de evaluación o revisión',
            ],
            [
                'name' => 'En revisión - IAO',
                'slug' => 'under_review_iao',
                'description' => 'Empleado en proceso de evaluación o revisión',
            ],
            [
                'name' => 'En revisión - Licenciada Ana Lucía',
                'slug' => 'under_review_lic',
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
            [
                'name' => 'Guardia Temporal',
                'slug' => 'temporary_guard',
                'description' => 'Empleado en estado de guardia temporal',
            ],
            [
                'name' => 'Suspendido',
                'slug' => 'suspended',
                'description' => 'Empleado actualmente suspendido',
            ],
            [
                'name' => 'Capacitación',
                'slug' => 'training',
                'description' => 'Empleado en proceso de capacitación',
            ],
            [
                'name' => 'Validación de cuenta',
                'slug' => 'account_validation',
                'description' => 'El empleado está pendiente de validación de cuenta.',
            ],
            [
                'name'        => 'Aprobación',
                'slug'        => 'approval',
                'description' => 'El empleado está pendiente de aprobación.',
            ],
            [
                'name'        => 'Asegurado',
                'slug'        => 'insured',
                'description' => 'El empleado está asegurado.',
            ],
            [
                'name'        => 'Acreditado',
                'slug'        => 'accredited',
                'description' => 'El empleado está acreditado.',
            ]
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
