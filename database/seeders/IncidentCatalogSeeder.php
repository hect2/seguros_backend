<?php

namespace Database\Seeders;

use App\Models\IncidentCatalog;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class IncidentCatalogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $incidents = [

            // ───── ALTAS ─────
            ['Alta en planilla adicional de SIGSESA', 'ALTA', 'laboral'],
            ['Alta por factura especial', 'ALTA', 'laboral'],
            ['Alta por licencia laboral', 'ALTA', 'laboral'],

            // ───── BAJAS ─────
            ['Baja a su solicitud', 'BAJA', 'laboral'],
            ['Baja por abandono', 'BAJA', 'laboral'],
            ['Baja por faltas al reglamento interno', 'BAJA', 'disciplinario'],
            ['Baja por no aprobar periodo de prueba', 'BAJA', 'laboral'],
            ['Baja por mutuo acuerdo', 'BAJA', 'laboral'],
            ['Baja por demanda', 'BAJA', 'legal'],
            ['Baja por fallecimiento', 'BAJA', 'laboral'],

            // ───── INCIDENTES ─────
            ['Disparo intencional', 'INCIDENTE', 'seguridad'],
            ['Armas hurtadas o consignadas', 'INCIDENTE', 'seguridad'],
            ['Colisiones de vehículos', 'INCIDENTE', 'vehicular'],
            ['Accidente laboral', 'INCIDENTE', 'laboral'],
            ['Accidente de motocicleta', 'INCIDENTE', 'vehicular'],
            ['Accidente común', 'INCIDENTE', 'laboral'],

            // ───── SERVICIOS / OPERACIONES ─────
            ['Servicio religioso', 'SERVICIO', 'operativo'],
            ['Práctica de tiro', 'SERVICIO', 'operativo'],
            ['Salida de custodio', 'SERVICIO', 'operativo'],
            ['En calidad de apoyo', 'SERVICIO', 'operativo'],
            ['En recuperación', 'SERVICIO', 'salud'],
            ['En capacitación', 'SERVICIO', 'formacion'],
            ['Polígono Verapaz', 'SERVICIO', 'formacion'],
            ['Capacitación y retroalimentación', 'SERVICIO', 'formacion'],
            ['Prueba de polígrafo', 'SERVICIO', 'control'],
            ['Entrega de uniformes', 'SERVICIO', 'logistica'],
            ['Técnico outsourcing', 'SERVICIO', 'tecnico'],
            ['Monitoreo Banrural', 'SERVICIO', 'cliente'],
            ['Supervisión administrativa y operativa', 'SERVICIO', 'supervision'],
            ['Entrevista de aspirantes', 'SERVICIO', 'rrhh'],
            ['Equipos bélicos', 'SERVICIO', 'logistica'],

            // ───── CONTROL / ESTADOS ─────
            ['Hospitalizado', 'CONTROL', 'salud'],
            ['Redundancia', 'CONTROL', 'operativo'],
            ['Traslado de agentes de seguridad', 'CONTROL', 'operativo'],
            ['Entrega de vehículo', 'CONTROL', 'logistica'],

            // ───── INCREMENTOS / DECREMENTOS ─────
            ['Incremento Banrural', 'CONTROL', 'cliente'],
            ['Incremento otros clientes', 'CONTROL', 'cliente'],
            ['Incremento de alarma', 'CONTROL', 'cliente'],
            ['Decremento Banrural', 'CONTROL', 'cliente'],
            ['Decremento otros clientes', 'CONTROL', 'cliente'],
            ['Decremento de alarma', 'CONTROL', 'cliente'],

            // ───── SUPERVISIONES ─────
            ['Supervisión por falla eléctrica', 'SERVICIO', 'supervision'],
            ['Supervisión por falla de comunicación', 'SERVICIO', 'supervision'],
            ['Supervisión nocturna', 'SERVICIO', 'supervision'],
            ['Supervisión Banrural', 'SERVICIO', 'supervision'],
            ['Supervisión otros clientes', 'SERVICIO', 'supervision'],
            ['Supervisión entrega de café y pan', 'SERVICIO', 'supervision'],

            // ───── SERVICIOS ESPECIALES ─────
            ['Servicio especial Banrural', 'SERVICIO', 'cliente'],
            ['Servicio especial otros clientes', 'SERVICIO', 'cliente'],
            ['Servicio especial temporal', 'SERVICIO', 'cliente'],
            ['Servicio especial temporal fin de año', 'SERVICIO', 'cliente'],

            // ───── PERMISOS ─────
            ['Permiso asuntos personales sin goce', 'PERMISO', 'rrhh'],
            ['Permiso emergencia familiar', 'PERMISO', 'rrhh'],
            ['Permiso por licencia laboral', 'PERMISO', 'rrhh'],
            ['Permiso por fallecimiento familiar', 'PERMISO', 'rrhh'],
            ['Permiso asistencia IGSS', 'PERMISO', 'rrhh'],
            ['Permiso por citación judicial', 'PERMISO', 'legal'],
            ['Permiso por matrimonio', 'PERMISO', 'rrhh'],
            ['Permiso por nacimiento de hijo', 'PERMISO', 'rrhh'],
            ['Permiso por maternidad', 'PERMISO', 'rrhh'],

            // ───── FALTAS / CONTROL ─────
            ['Falta sin justificación', 'CONTROL', 'disciplinario'],
            ['Falta por detención', 'CONTROL', 'legal'],
            ['Vacaciones', 'CONTROL', 'rrhh'],

            // ───── CERTIFICACIONES ─────
            ['Certificación DIGESSP', 'CONTROL', 'certificacion'],
            ['Certificación DIGECAM', 'CONTROL', 'certificacion'],
        ];

        foreach ($incidents as [$name, $type, $group]) {
            IncidentCatalog::firstOrCreate([
                'slug' => Str::slug($name)
            ], [
                'name' => $name,
                'type' => $type,
                'group' => $group,
                'active' => true
            ]);
        }
    }
}
