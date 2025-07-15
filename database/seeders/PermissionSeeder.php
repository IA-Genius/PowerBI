<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        // 1) Define aquí todos los permisos de tu sistema
        $permisos = [
            // gestión de módulos
            'gestionar roles',
            'gestionar usuarios',
            'gestionar carteras',
            'gestionar reportes',

            // permisos de vista/lectura
            'view roles',
            'view usuarios',
            'view carteras',
            'view reportes',
        ];

        // 2) Créalos (o busca si ya existen)
        foreach ($permisos as $perm) {
            Permission::firstOrCreate(
                ['name' => $perm, 'guard_name' => 'web']
            );
        }

        // 3) Crea roles por defecto y asígnales permisos
        $rolAdmin = Role::firstOrCreate(
            ['name' => 'admin', 'guard_name' => 'web']
        );
        // dar al admin *todos* los permisos
        $rolAdmin->syncPermissions(Permission::all());

        $rolViewer = Role::firstOrCreate(
            ['name' => 'viewer', 'guard_name' => 'web']
        );
        // sólo permisos de vista
        $rolViewer->syncPermissions(
            Permission::where('name', 'like', 'view %')->get()
        );

        // …y así sucesivamente con los roles que necesites
    }
}
