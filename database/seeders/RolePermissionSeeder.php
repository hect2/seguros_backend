<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Crear roles
        $super_admin = Role::create(['name' => 'Super Administrador']);
        $admin = Role::create(['name' => 'Administrador']);
        $supervisor = Role::create(['name' => 'Supervidor']);
        $operator = Role::create(['name' => 'Operador']);

        // 🏆 Super Admin: todos los permisos
        $super_admin->syncPermissions(Permission::all());

        // 🧭 Administrador: casi todos (sin eliminar)
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
        ]);

        // 👨‍💼 Supervisor: solo revisión y visualización
        $supervisor->syncPermissions([
            'dashboard_view_reports',
            'incidents_view',
            'employees_view',
            'employees_approve',
            'reports_view',
            'districts_view',
            'offices_view',
            'users_view',
        ]);

        // ⚙️ Operador: tareas básicas (solo ver/crear)
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
