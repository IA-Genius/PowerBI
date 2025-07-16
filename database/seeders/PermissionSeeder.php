<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use App\Models\Role;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permisos = [
            'gestionar roles',
            'gestionar usuarios',
            'gestionar carteras',
            'gestionar reportes',
        ];

        foreach ($permisos as $perm) {
            Permission::firstOrCreate(
                ['name' => $perm, 'guard_name' => 'web']
            );
        }

        // Rol admin: todos los permisos
        $rolAdmin = Role::firstOrCreate(
            ['name' => 'admin', 'guard_name' => 'web']
        );
        $rolAdmin->syncPermissions(Permission::all());

        // Rol viewer: sin permisos
        $rolViewer = Role::firstOrCreate(
            ['name' => 'viewer', 'guard_name' => 'web']
        );
        $rolViewer->syncPermissions([]);
    }
}
