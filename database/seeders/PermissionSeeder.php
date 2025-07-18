<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Module;
use App\Models\Permission;
use App\Models\Role;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {

        $modulosConPermisos = [
            'Roles' => [
                'roles.index',
                'roles.create',
                'roles.store',
                'roles.edit',
                'roles.update',
                'roles.destroy',
            ],
            'Usuarios' => [
                'usuarios.index',
                'usuarios.create',
                'usuarios.store',
                'usuarios.edit',
                'usuarios.update',
                'usuarios.destroy',
            ],
            'Carteras' => [
                'carteras.index',
                'carteras.create',
                'carteras.store',
                'carteras.edit',
                'carteras.update',
                'carteras.destroy',
            ],
            'Reportes' => [
                'reportes.index',
                'reportes.edit',
                'reportes.update',
            ],
            'Vodafone' => [
                'vodafone.index',
                'vodafone.create',
                'vodafone.store',
                'vodafone.edit',
                'vodafone.update',
                'vodafone.destroy',
                'vodafone.view-global', // 👈 nuevo permiso
            ],

        ];

        foreach ($modulosConPermisos as $moduloNombre => $permisos) {
            $modulo = Module::firstOrCreate(['name' => $moduloNombre]);

            foreach ($permisos as $permisoNombre) {
                Permission::firstOrCreate([
                    'name' => $permisoNombre,
                    'guard_name' => 'web',
                    'module_id' => $modulo->id,
                ]);
            }
        }

        // Crear roles
        $rolAdmin = Role::firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'web',
        ]);
        $rolAdmin->syncPermissions(Permission::all());

        $rolViewer = Role::firstOrCreate([
            'name' => 'viewer',
            'guard_name' => 'web',
        ]);

        $rolViewer->syncPermissions([]);

        $rolSupervisor = Role::firstOrCreate([
            'name' => 'supervisor vodafone',
            'guard_name' => 'web',
        ]);

        $rolSupervisor->syncPermissions([
            'vodafone.index',
            'vodafone.view-global',
        ]);
    }
}
