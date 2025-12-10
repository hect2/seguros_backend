<?php

namespace Database\Seeders;

use App\Models\District;
use App\Models\EmployeeStatus;
use App\Models\Office;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $roles = Role::all()->pluck('name')->toArray();

        foreach ($roles as $roleName) {
            // 🔹 Crea un usuario para cada rol
            $user = User::firstOrCreate(
                ['email' => strtolower(str_replace(' ', '_', $roleName)) . '@example.com'],
                [
                    'name' => $roleName,
                    'password' => Hash::make('password'), // 👈 puedes cambiarlo
                    'status' =>  EmployeeStatus::inRandomOrder()->first()->id,
                    'dpi' => fake()->numerify('#############'),
                    'phone' => fake()->phoneNumber(),
                    'district' => json_encode([]),
                    'office' => json_encode([]),
                    'observations' => fake()->sentence(),
                    'last_changed_password' => now(),
                ]
            );

            // 🔹 Asignar el rol
            $user->assignRole($roleName);

            if ($roleName == 'Administrador') {
                $district = District::where("code", "DINOR")->first();
                $user->update([
                    'district' => [$district->id,],
                    'office' => [Office::where("district_id", $district->id)->first()->id,],
                ]);
            }
            if ($roleName == 'Supervidor') {
                $district = District::where("code", "DICE")->first();
                $user->update([
                    'district' => [$district->id,],
                    'office' => [Office::where("district_id", $district->id)->first()->id,],
                ]);
            }
            if ($roleName == 'Operador') {
                $district = District::where("code", "DISO_SUR")->first();
                $user->update([
                    'district' => [$district->id,],
                    'office' => [Office::where("district_id", $district->id)->first()->id,],
                ]);
            }

            echo "✅ Usuario {$user->email} con rol {$roleName} creado.\n";
        }
    }
}
