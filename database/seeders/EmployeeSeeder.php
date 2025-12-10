<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\EmployeeStatus;
use App\Models\Office;
use App\Models\Position;
use App\Models\PositionType;
use App\Models\Tracking;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = EmployeeStatus::all();
        $users = User::all();
        $offices = Office::all();
        $positionTypes = PositionType::all();

        for ($i = 0; $i < 20; $i++) {
            $employee = Employee::create([
                'full_name' => fake()->name(),
                'dpi' => fake()->unique()->numerify('#############'),
                'birth_date' => fake()->date(),
                'phone' => fake()->numerify('########'),
                'email' => fake()->unique()->safeEmail(),
                'files' => null,
                'status_id' => $statuses->random()->id,
                'digessp_fecha_vencimiento' => fake()->optional()->date(),
            ]);

            // Posición del empleado
            $office = $offices->random();
            Position::create([
                'employee_id' => $employee->id,
                'office_id' => $office->id,
                'district_id' => $office->district_id,
                'admin_position_type_id' => $positionTypes->random()->id,
                'operative_position_type_id' => fake()->optional()->randomElement($positionTypes->pluck('id')->toArray()),
                'initial_salary' => fake()->numberBetween(3000, 5000),
                'bonuses' => fake()->numberBetween(0, 500),
                'status' => 1,
            ]);

            // Archivos simulados
            $files = [];
            for ($f = 0; $f < fake()->numberBetween(1, 3); $f++) {
                $files[] = [
                    'uuid' => str()->uuid(),
                    'name' => fake()->word() . '.pdf',
                    'type' => fake()->randomElement(['pdf', 'jpg', 'png']),
                    'date_emission' => fake()->date(),
                    'status' => fake()->randomElement([0, 1, 2])
                ];
            }

            $employee->update([
                'files' => [
                    'description_files' => fake()->sentence(),
                    'files' => $files
                ]
            ]);

            // Trackings iniciales
            $responsible = $users->random()->id;
            Tracking::create([
                'employee_id' => $employee->id,
                'name' => 'new_client',
                'responsible' => $responsible,
                'approval_date' => now(),
                'status' => 1,
                'description' => 'Carga de nuevo cliente',
            ]);

            Tracking::create([
                'employee_id' => $employee->id,
                'name' => 'documents_review',
                'responsible' => fake()->boolean() ? $users->random()->id : null,
                'approval_date' => null,
                'status' => fake()->randomElement([0, 2]),
                'description' => 'Revisión de documentos',
            ]);

            Tracking::create([
                'employee_id' => $employee->id,
                'name' => 'validate_account',
                'responsible' => null,
                'approval_date' => null,
                'status' => 0,
                'description' => 'Validación de cuenta',
            ]);

            Tracking::create([
                'employee_id' => $employee->id,
                'name' => 'approve_client',
                'responsible' => null,
                'approval_date' => null,
                'status' => 0,
                'description' => 'Aprobación',
            ]);

            // Simular actualización de status similar a tu update
            $newStatus = $statuses->random();
            $user_responsible_id = fake()->boolean() ? $users->random()->id : null;

            if ($newStatus->slug == 'under_review' && $user_responsible_id) {
                $employee->trackings()->where('name', 'documents_review')->update([
                    'status' => 2,
                    'responsible' => $user_responsible_id,
                    'approval_date' => null,
                ]);
            } elseif ($newStatus->slug == 'account_validation' && $user_responsible_id) {
                $employee->trackings()->where('name', 'documents_review')->update(['status' => 1, 'approval_date' => now()]);
                $employee->trackings()->where('name', 'validate_account')->update([
                    'status' => 2,
                    'responsible' => $user_responsible_id,
                    'approval_date' => null
                ]);
            } elseif ($newStatus->slug == 'approval' && $user_responsible_id) {
                $employee->trackings()->where('name', 'validate_account')->update(['status' => 1, 'approval_date' => now()]);
                $employee->trackings()->where('name', 'approve_client')->update([
                    'status' => 2,
                    'responsible' => $user_responsible_id,
                    'approval_date' => null
                ]);
            } elseif ($newStatus->slug == 'active') {
                $employee->trackings()->where('name', 'approve_client')->update([
                    'status' => 1,
                    'approval_date' => now()
                ]);
            }

            $employee->update(['status_id' => $newStatus->id]);
        }
    }
}
