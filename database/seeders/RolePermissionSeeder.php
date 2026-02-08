<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // =========================
        // ROLES EXISTENTES
        // =========================
        $super_admin = Role::firstOrCreate(['name' => 'Super Administrador']);
        $admin = Role::firstOrCreate(['name' => 'Administrador']);
        $supervisor = Role::firstOrCreate(['name' => 'Supervisor']);
        $operator = Role::firstOrCreate(['name' => 'Operador']);

        // =========================
        // ROLES DEL FLUJO AUTOMÁTICO
        // =========================
        $director = Role::firstOrCreate(['name' => 'Director de Dependencia']);
        $talento_humano = Role::firstOrCreate(['name' => 'Talento Humano']);
        $iao = Role::firstOrCreate(['name' => 'IAO']);
        $ana_lucia = Role::firstOrCreate(['name' => 'Licenciada Ana Lucía']);
        $cfe = Role::firstOrCreate(['name' => 'CFE Validación']);
        $sg = Role::firstOrCreate(['name' => 'SG Autorización']);

        // =========================
        // SUPER ADMIN
        // =========================
        $super_admin->syncPermissions(Permission::all());

        // =========================
        // ADMINISTRADOR
        // =========================
        $admin->syncPermissions([
            'dashboard_view_reports',
            'dashboard_view_charts',

            'incidents_view',
            'incidents_create',
            'incidents_edit',

            'employees_view',
            'employees_create_or_import',
            'employees_edit',
            'employees_approve',
            'employees_bonus',

            'reports_view',
            'reports_download',

            'districts_view',
            'districts_create_or_import',
            'districts_download',
            'districts_edit',

            'offices_view',
            'offices_create_or_import',
            'offices_download',
            'offices_edit',

            'users_view',
            'users_create',
            'users_edit',

            'business_view',
            'business_create',
            'business_edit',

            'employee_status_view',
            'employee_status_create',
            'employee_status_edit',

            'employee_positions_view',
            'employee_positions_create',
            'employee_positions_edit',

            'service_positions_view',
            'service_positions_create',
            'service_positions_edit',

            // flujo
            'requests_view',
            'requests_validate',
            'requests_authorize',
        ]);

        // =========================
        // FLUJO AUTOMÁTICO DE ALTAS / BAJAS
        // =========================

        // 1️⃣ Director de Dependencia – registra la solicitud
        $director->syncPermissions([
            'dashboard_view_reports',
            'dashboard_view_charts',
            'employees_view',
            'employees_create_or_import',
            'employees_edit',
            'employees_approve',
            'employees_bonus',
            'requests_view',
            'requests_create',
        ]);

        // 2️⃣ Talento Humano – revisa documentación
        $talento_humano->syncPermissions([
            'dashboard_view_reports',
            'dashboard_view_charts',
            'employees_view',
            'employees_create_or_import',
            'employees_edit',
            'employees_approve',
            'employees_bonus',
            'requests_view',
            'requests_review_th',
        ]);

        // 3️⃣ IAO – revisa documentación
        $iao->syncPermissions([
            'dashboard_view_reports',
            'dashboard_view_charts',
            'employees_view',
            'employees_create_or_import',
            'employees_edit',
            'employees_approve',
            'employees_bonus',
            'requests_view',
            'requests_review_iao',
        ]);

        // 4️⃣ Licenciada Ana Lucía – revisa documentación
        $ana_lucia->syncPermissions([
            'dashboard_view_reports',
            'dashboard_view_charts',
            'employees_view',
            'employees_create_or_import',
            'employees_edit',
            'employees_approve',
            'employees_bonus',
            'requests_view',
            'requests_review_lic',
        ]);

        // 5️⃣ CFE – valida documentación
        $cfe->syncPermissions([
            'dashboard_view_reports',
            'dashboard_view_charts',
            'employees_view',
            'employees_create_or_import',
            'employees_edit',
            'employees_approve',
            'employees_bonus',
            'requests_view',
            'requests_validate',
        ]);

        // 6️⃣ SG – autoriza la solicitud
        $sg->syncPermissions([
            'dashboard_view_reports',
            'dashboard_view_charts',
            'employees_view',
            'employees_create_or_import',
            'employees_edit',
            'employees_approve',
            'employees_bonus',
            'requests_view',
            'requests_authorize',
        ]);

        // =========================
        // SUPERVISOR
        // =========================
        $supervisor->syncPermissions([
            'dashboard_view_reports',
            'incidents_view',
            'employees_view',
            'employees_approve',
            'reports_view',
            'districts_view',
            'offices_view',
            'users_view',
            'requests_view',
        ]);

        // =========================
        // OPERADOR
        // =========================
        $operator->syncPermissions([
            'incidents_view',
            'incidents_create',
            'employees_view',
            'reports_view',
            'districts_view',
            'offices_view',
            'users_view',
        ]);
    }
}
