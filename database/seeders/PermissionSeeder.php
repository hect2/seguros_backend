<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // Dashboard
            'dashboard_view_reports',
            'dashboard_view_charts',

            // Incidents
            'incidents_view',
            'incidents_create',
            'incidents_edit',

            // Employees
            'employees_view',
            'employees_create_or_import',
            'employees_edit',
            'employees_approve',
            'employees_bonus',

            // Reports
            'reports_view',
            'reports_download',

            // Districts
            'districts_view',
            'districts_create_or_import',
            'districts_download',
            'districts_edit',
            'districts_delete',

            // Offices
            'offices_view',
            'offices_create_or_import',
            'offices_download',
            'offices_edit',
            'offices_delete',

            // Users
            'users_view',
            'users_create',
            'users_edit',

            // Business
            'business_view',
            'business_create',
            'business_edit',

            // Catalogs
            'employee_status_view',
            'employee_status_create',
            'employee_status_edit',

            'employee_positions_view',
            'employee_positions_create',
            'employee_positions_edit',

            // Service Positions
            'service_positions_view',
            'service_positions_create',
            'service_positions_edit',

            // =========================
            // FLUJO ALTAS / BAJAS
            // =========================
            'requests_create',
            'requests_view',
            'requests_review_th',
            'requests_review_iao',
            'requests_review_lic',
            'requests_validate',
            'requests_authorize',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web',]);
        }
    }
}
