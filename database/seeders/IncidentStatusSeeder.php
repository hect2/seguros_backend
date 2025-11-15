<?php

namespace Database\Seeders;

use App\Models\IncidentStatus;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class IncidentStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $status = [
            ['name' => 'Pendiente',     'slug' => 'pending',      'description' => 'Incidente registrado pero no iniciado'],
            ['name' => 'En progreso',   'slug' => 'in_progress',  'description' => 'Incidente siendo atendido'],
            ['name' => 'Resuelto',      'slug' => 'resolved',     'description' => 'Solución aplicada, pendiente de verificación'],
            ['name' => 'Cerrado',       'slug' => 'closed',       'description' => 'Incidente cerrado definitivamente'],
        ];

        foreach ($status as $key => $value) {
            IncidentStatus::updateOrCreate(
                ['slug'=> $value['slug']],
                [
                    'name'=> $value['name'],
                    'description'=> $value['description'],
                ]
            );
        }
    }
}
