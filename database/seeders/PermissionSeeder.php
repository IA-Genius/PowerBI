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
            'Roles' => ['ver', 'crear', 'guardar', 'editar', 'eliminar'],
            'Usuarios' => ['ver', 'crear', 'guardar', 'editar', 'eliminar'],
            'Carteras' => ['ver', 'crear', 'guardar', 'editar', 'eliminar'],
            'Reportes' => ['ver', 'crear', 'guardar', 'editar', 'eliminar'],
            'Vodafone' => [
                'ver',           // ver registros
                'crear',
                'guardar',
                'editar',
                'eliminar',
                'ver-global',    // ver registros de otros
                'asignar',       // asignar registros
                'recibe-asignacion', // se puede asignar a este usuario
            ],
        ];

        foreach ($modulosConPermisos as $moduloNombre => $acciones) {
            $modulo = Module::firstOrCreate(['name' => $moduloNombre]);
            $moduloSlug = strtolower($moduloNombre);

            foreach ($acciones as $accion) {
                $permisoNombre = "{$moduloSlug}.{$accion}";
                Permission::firstOrCreate([
                    'name' => $permisoNombre,
                    'guard_name' => 'web',
                    'module_id' => $modulo->id,
                ]);
            }
        }

        // ROL ADMIN
        $rolAdmin = Role::firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'web',
        ]);
        $rolAdmin->syncPermissions(Permission::all());

        // ROL COORDINADOR
        $rolCoordinador = Role::firstOrCreate([
            'name' => 'coordinador-vodafone',
            'guard_name' => 'web',
        ]);
        $rolCoordinador->syncPermissions([
            'vodafone.ver',
            'vodafone.editar',
            'vodafone.guardar',
            'vodafone.ver-global',
            'vodafone.asignar',
        ]);

        // ROL FILTRADOR
        $rolFiltrador = Role::firstOrCreate([
            'name' => 'filtrador-vodafone',
            'guard_name' => 'web',
        ]);
        $rolFiltrador->syncPermissions([
            'vodafone.ver',
            'vodafone.guardar',
            'vodafone.recibe-asignacion',
        ]);
    }
}
