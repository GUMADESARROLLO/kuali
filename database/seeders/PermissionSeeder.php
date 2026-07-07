<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'gestionar catalogos',
            'gestionar usuarios',
            'reasignar tickets',
            'reclasificar tickets',
            'comentar interno',
            'ver todos los tickets',
            'exportar reportes',
        ];

        foreach ($permissions as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        $admin = Role::findByName('admin_it', 'web');
        $admin->syncPermissions(Permission::all());

        $agent = Role::findByName('agente_it', 'web');
        $agent->syncPermissions([
            'comentar interno',
        ]);

        $userRole = Role::findByName('usuario', 'web');
        $userRole->syncPermissions([]);
    }
}
