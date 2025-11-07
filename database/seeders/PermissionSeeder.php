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

            // News
            'news_view',
            'news_create',
            'news_delete',
            'news_edit',

            // Employees
            'employees_view',
            'employees_create',
            'employees_edit',
            'employees_approve',

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
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }
    }
}
