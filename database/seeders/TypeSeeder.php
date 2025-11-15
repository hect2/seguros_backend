<?php

namespace Database\Seeders;

use App\Models\Type;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            ['name' => 'Importantes', 'description' => 'Incidentes o eventos importantes.'],
            ['name' => 'Negativas', 'description' => 'Eventos con impacto negativo o sanciones.'],
            ['name' => 'Supervisiones', 'description' => 'Supervisiones de personal o tareas.'],
            ['name' => 'Permisos', 'description' => 'Solicitudes o registros de permisos.'],
            ['name' => 'Faltando', 'description' => 'Reportes de ausencias o faltas.'],
            ['name' => 'Servicios Especiales', 'description' => 'Actividades o servicios fuera de lo habitual.'],
            ['name' => 'Vacaciones', 'description' => 'Gestión de vacaciones del personal.'],
            ['name' => 'Rutinas', 'description' => 'Tareas diarias o rutinarias.'],
        ];

        Type::insert($types);
    }
}
