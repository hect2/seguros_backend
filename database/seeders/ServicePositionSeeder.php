<?php

namespace Database\Seeders;

use App\Models\ServicePosition;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ServicePositionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $servicePositions = [

            // ===== BANRURAL =====
            [
                'business_id' => 1,
                'name' => 'Banrural - Agencia Zona 1',
                'location' => 'Zona 1, Ciudad de Guatemala',
                'shift' => 'Diurno',
                'service_type' => 'Agente armado',
                'active' => true,
            ],
            [
                'business_id' => 1,
                'name' => 'Banrural - Agencia Mixco',
                'location' => 'Mixco, Guatemala',
                'shift' => '24x24',
                'service_type' => 'Agente armado',
                'active' => true,
            ],
            [
                'business_id' => 1,
                'name' => 'Banrural - Centro de Monitoreo',
                'location' => 'Zona 10, Ciudad de Guatemala',
                'shift' => 'Nocturno',
                'service_type' => 'Monitoreo',
                'active' => true,
            ],

            // ===== CLIENTE PRIVADO =====
            [
                'business_id' => 2,
                'name' => 'Residencial Las Flores',
                'location' => 'Carretera a El Salvador',
                'shift' => '12x12',
                'service_type' => 'Seguridad perimetral',
                'active' => true,
            ],
            [
                'business_id' => 2,
                'name' => 'Residencial Las Flores - Garita Principal',
                'location' => 'Carretera a El Salvador',
                'shift' => '24x24',
                'service_type' => 'Control de accesos',
                'active' => true,
            ],

            // ===== INDUSTRIAL =====
            [
                'business_id' => 3,
                'name' => 'Bodega Central - Ministerio X',
                'location' => 'Villa Nueva',
                'shift' => 'Nocturno',
                'service_type' => 'Custodia',
                'active' => true,
            ],
            [
                'business_id' => 3,
                'name' => 'Planta Industrial - Ministerio X',
                'location' => 'Amatitlán',
                'shift' => 'Turnos rotativos',
                'service_type' => 'Seguridad industrial',
                'active' => true,
            ],

            // ===== EVENTUALES =====
            [
                'business_id' => 4,
                'name' => 'Evento Especial - Fin de Año',
                'location' => 'Zona 15',
                'shift' => 'Temporal',
                'service_type' => 'Refuerzo de seguridad',
                'active' => true,
            ],
            [
                'business_id' => 4,
                'name' => 'Custodia Ejecutiva - Traslado',
                'location' => 'Área metropolitana',
                'shift' => 'Según programación',
                'service_type' => 'Seguridad ejecutiva',
                'active' => true,
            ],
        ];

        foreach ($servicePositions as $position) {
            ServicePosition::firstOrCreate($position);
        }
    }
}
