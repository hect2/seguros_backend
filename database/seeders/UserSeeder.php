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
                    'status' => EmployeeStatus::inRandomOrder()->first()->id,
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
                    'office' => [Office::where("district_id", $district->id)->where("code", 'OF008')->first()->id,],
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

        $roles_admin = Role::where('name', 'Administrador')->first();
        $user = User::firstOrCreate(
            ['email' => strtolower(str_replace(' ', '_', $roles_admin->name)) . rand(2, 20) . 'DINOR' . '@example.com'],
            [
                'name' => $roles_admin->name,
                'password' => Hash::make('password'), // 👈 puedes cambiarlo
                'status' => EmployeeStatus::inRandomOrder()->first()->id,
                'dpi' => fake()->numerify('#############'),
                'phone' => fake()->phoneNumber(),
                'district' => json_encode([]),
                'office' => json_encode([]),
                'observations' => fake()->sentence(),
                'last_changed_password' => now(),
            ]
        );
        $user->assignRole($roles_admin->name);

        if ($roles_admin->name == 'Administrador') {
            $district = District::where("code", "DINOR")->first();
            $user->update([
                'district' => [$district->id,],
                'office' => [Office::where("district_id", $district->id)->where("code", 'OF009')->first()->id,],
            ]);
        }


        $user = User::firstOrCreate(
            ['email' => strtolower(str_replace(' ', '_', $roles_admin->name)) . rand(2, 20) . 'DICE' . '@example.com'],
            [
                'name' => $roles_admin->name,
                'password' => Hash::make('password'), // 👈 puedes cambiarlo
                'status' => EmployeeStatus::inRandomOrder()->first()->id,
                'dpi' => fake()->numerify('#############'),
                'phone' => fake()->phoneNumber(),
                'district' => json_encode([]),
                'office' => json_encode([]),
                'observations' => fake()->sentence(),
                'last_changed_password' => now(),
            ]
        );
        $user->assignRole($roles_admin->name);

        if ($roles_admin->name == 'Administrador') {
            $district = District::where("code", "DICE")->first();
            $user->update([
                'district' => [$district->id,],
                'office' => [Office::where("district_id", $district->id)->where("code", 'OF004')->first()->id,],
            ]);
        }

        $user = User::firstOrCreate(
            ['email' => strtolower(str_replace(' ', '_', $roles_admin->name)) . rand(2, 20) . 'DISO_SUR' . '@example.com'],
            [
                'name' => $roles_admin->name,
                'password' => Hash::make('password'), // 👈 puedes cambiarlo
                'status' => EmployeeStatus::inRandomOrder()->first()->id,
                'dpi' => fake()->numerify('#############'),
                'phone' => fake()->phoneNumber(),
                'district' => json_encode([]),
                'office' => json_encode([]),
                'observations' => fake()->sentence(),
                'last_changed_password' => now(),
            ]
        );
        $user->assignRole($roles_admin->name);

        if ($roles_admin->name == 'Administrador') {
            $district = District::where("code", "DISO_SUR")->first();
            $user->update([
                'district' => [$district->id,],
                'office' => [Office::where("district_id", $district->id)->where("code", 'OF012')->first()->id,],
            ]);
        }
    }
}
