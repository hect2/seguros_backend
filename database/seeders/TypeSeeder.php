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
            ['name' => 'Importantes',           'slug' => 'important',          'description' => 'Incidentes o eventos importantes.'],
            ['name' => 'Negativas',             'slug' => 'negative',           'description' => 'Eventos con impacto negativo o sanciones.'],
            ['name' => 'Supervisiones',         'slug' => 'supervisions',       'description' => 'Supervisiones de personal o tareas.'],
            ['name' => 'Permisos',              'slug' => 'permissions',        'description' => 'Solicitudes o registros de permisos.'],
            ['name' => 'Faltando',              'slug' => 'absences',           'description' => 'Reportes de ausencias o faltas.'],
            ['name' => 'Servicios Especiales',  'slug' => 'special-services',   'description' => 'Actividades o servicios fuera de lo habitual.'],
            ['name' => 'Vacaciones',            'slug' => 'vacations',          'description' => 'Gestión de vacaciones del personal.'],
            ['name' => 'Rutinas',               'slug' => 'routines',           'description' => 'Tareas diarias o rutinarias.'],
        ];

        foreach ($types as $key => $value) {
            Type::updateOrCreate(
                ['slug'=> $value['slug']],
                [
                    'name'=> $value['name'],
                    'description'=> $value['description'],
                ]
            );
        }
    }
}
