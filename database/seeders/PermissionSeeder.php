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
            'Roles' => ['ver', 'crear', 'editar', 'eliminar'],
            'Usuarios' => ['ver', 'crear',  'editar', 'eliminar'],
            'Carteras' => ['ver', 'crear',  'editar', 'eliminar'],
            'Reportes' => ['ver', 'crear', 'editar', 'eliminar'],
            'Vodafone' => [
                'ver',           // ver registros
                'crear',
                'editar',
                'eliminar',
                'ver-global',
                'importar',   // importar registros de otros
                'asignar',       // asignar registros
                'recibe-asignacion', // se puede asignar a este usuario
                'ver-historial', // ver historial de asignaciones
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


        Role::firstOrCreate([
            'name' => 'asesor',
            'guard_name' => 'web',
        ]);

        // ROL COORDINADOR
        $rolCoordinador = Role::firstOrCreate([
            'name' => 'coordinador-vodafone',
            'guard_name' => 'web',
        ]);
        $rolCoordinador->syncPermissions([
            'vodafone.ver',
            'vodafone.editar',
            'vodafone.crear',
            'vodafone.importar',
            'vodafone.ver-global',
            'vodafone.asignar',
            'vodafone.ver-historial',
        ]);

        // ROL FILTRADOR
        $rolFiltrador = Role::firstOrCreate([
            'name' => 'filtrador-vodafone',
            'guard_name' => 'web',
        ]);

        $rolFiltrador->syncPermissions([
            'vodafone.ver',
            'vodafone.editar',
            'vodafone.recibe-asignacion',
        ]);
        // ROL ASESOR VODAFONE
        $rolAsesorVodafone = Role::firstOrCreate([
            'name' => 'asesor-vodafone',
            'guard_name' => 'web',
        ]);
        $rolAsesorVodafone->syncPermissions([
            'vodafone.ver',
        ]);
    }
}
