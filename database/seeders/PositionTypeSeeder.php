<?php

namespace Database\Seeders;

use App\Models\PositionType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PositionTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $position_types = [
            [
                'name' => 'Administrator',
                'category' => 'Administrative',
                'slug' => 'administrator',
                'description' => 'Responsible for office administrative tasks'
            ],
            [
                'name' => 'Operator',
                'category' => 'Operational',
                'slug' => 'operator',
                'description' => 'Performs field or operational tasks'
            ],
            [
                'name' => 'Supervisor',
                'category' => 'Administrative',
                'slug' => 'supervisor',
                'description' => 'Supervises and coordinates staff activities'
            ],

            // ───── ADMINISTRATIVE ─────
            [
                'name' => 'Gerente General',
                'category' => 'Administrative',
                'slug' => 'gerente-general',
                'description' => 'Responsable de la dirección general de la empresa'
            ],
            [
                'name' => 'Subgerente General',
                'category' => 'Administrative',
                'slug' => 'subgerente-general',
                'description' => 'Apoya al gerente general en la toma de decisiones'
            ],
            [
                'name' => 'Auxiliar de Gerencia',
                'category' => 'Administrative',
                'slug' => 'auxiliar-gerencia',
                'description' => 'Soporte administrativo a gerencia'
            ],
            [
                'name' => 'Talento Humano',
                'category' => 'Administrative',
                'slug' => 'talento-humano',
                'description' => 'Gestión del personal y recursos humanos'
            ],
            [
                'name' => 'Auxiliar de Talento Humano',
                'category' => 'Administrative',
                'slug' => 'auxiliar-talento-humano',
                'description' => 'Apoyo operativo al área de recursos humanos'
            ],
            [
                'name' => 'Recepcionista',
                'category' => 'Administrative',
                'slug' => 'recepcionista',
                'description' => 'Atención y recepción de visitas y llamadas'
            ],

            // ───── DIRECTIVOS ─────
            [
                'name' => 'Director de Logística',
                'category' => 'Operational',
                'slug' => 'director-logistica',
                'description' => 'Gestión estratégica de logística y suministros'
            ],
            [
                'name' => 'Director SEMIS´D',
                'category' => 'Administrative',
                'slug' => 'director-semisd',
                'description' => 'Dirección del área SEMIS´D'
            ],
            [
                'name' => 'Director de Academia',
                'category' => 'Academic',
                'slug' => 'director-academia',
                'description' => 'Responsable de formación y capacitación'
            ],
            [
                'name' => 'Director de Distrito',
                'category' => 'Administrative',
                'slug' => 'director-distrito',
                'description' => 'Dirección operativa por distrito'
            ],
            [
                'name' => 'Subdirector de Distrito',
                'category' => 'Administrative',
                'slug' => 'subdirector-distrito',
                'description' => 'Apoyo al director de distrito'
            ],
            [
                'name' => 'Director de Dependencia',
                'category' => 'Administrative',
                'slug' => 'director-dependencia',
                'description' => 'Responsable de una dependencia específica'
            ],
            [
                'name' => 'Subdirector de Dependencia',
                'category' => 'Administrative',
                'slug' => 'subdirector-dependencia',
                'description' => 'Apoyo al director de dependencia'
            ],

            // ───── OPERATIONS ─────
            [
                'name' => 'Operaciones',
                'category' => 'Operational',
                'slug' => 'operaciones',
                'description' => 'Ejecución de actividades operativas'
            ],
            [
                'name' => 'Auxiliar de Operaciones',
                'category' => 'Operational',
                'slug' => 'auxiliar-operaciones',
                'description' => 'Soporte a las operaciones diarias'
            ],
            [
                'name' => 'Guarda Almacén',
                'category' => 'Operational',
                'slug' => 'guarda-almacen',
                'description' => 'Control y resguardo de inventario'
            ],
            [
                'name' => 'Conserje',
                'category' => 'Operational',
                'slug' => 'conserje',
                'description' => 'Mantenimiento y apoyo general'
            ],

            // ───── SECURITY ─────
            [
                'name' => 'Agente de Seguridad',
                'category' => 'Security',
                'slug' => 'agente-seguridad',
                'description' => 'Protección y vigilancia de instalaciones'
            ],
            [
                'name' => 'Seguridad Ejecutiva',
                'category' => 'Security',
                'slug' => 'seguridad-ejecutiva',
                'description' => 'Protección personalizada a ejecutivos'
            ],
            [
                'name' => 'Custodio',
                'category' => 'Security',
                'slug' => 'custodio',
                'description' => 'Custodia de personas o bienes'
            ],
            [
                'name' => 'Jefe de Grupo',
                'category' => 'Security',
                'slug' => 'jefe-grupo',
                'description' => 'Lidera un grupo de agentes de seguridad'
            ],
            [
                'name' => 'Supervisor',
                'category' => 'Security',
                'slug' => 'supervisor',
                'description' => 'Supervisa personal operativo y de seguridad'
            ],

            // ───── TRANSPORTE ─────
            [
                'name' => 'Piloto',
                'category' => 'Operational',
                'slug' => 'piloto',
                'description' => 'Conducción de vehículos asignados'
            ],
            [
                'name' => 'Piloto de Unidad Blindada',
                'category' => 'Security',
                'slug' => 'piloto-unidad-blindada',
                'description' => 'Conducción de vehículos blindados'
            ],

            // ───── ESURAM ─────
            [
                'name' => 'ESURAM',
                'category' => 'Operational',
                'slug' => 'esuram',
                'description' => 'Personal asignado a ESURAM'
            ],
            [
                'name' => 'Auxiliar de ESURAM',
                'category' => 'Operational',
                'slug' => 'auxiliar-esuram',
                'description' => 'Apoyo operativo a ESURAM'
            ],

            // ───── TÉCNICOS ─────
            [
                'name' => 'Operador de Consola',
                'category' => 'Technical',
                'slug' => 'operador-consola',
                'description' => 'Monitoreo y operación de sistemas'
            ],
            [
                'name' => 'Coordinador Técnico',
                'category' => 'Technical',
                'slug' => 'coordinador-tecnico',
                'description' => 'Coordinación de recursos técnicos'
            ],

            // ───── PROYECTOS ─────
            [
                'name' => 'Jefe de Proyecto',
                'category' => 'Administrative',
                'slug' => 'jefe-proyecto',
                'description' => 'Gestión y ejecución de proyectos'
            ],
            [
                'name' => 'Receptor',
                'category' => 'Operational',
                'slug' => 'receptor',
                'description' => 'Recepción y control de información o bienes'
            ],
        ];


        foreach ($position_types as $key => $value) {
            PositionType::updateOrCreate(
                ['slug' => $value['slug']],
                [
                    'name' => $value['name'],
                    'category' => $value['category'],
                    'description' => $value['description'],
                ]
            );
        }
    }
}
