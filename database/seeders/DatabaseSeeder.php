<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Cartera;
use App\Models\Reporte;
use Illuminate\Database\Seeder;
use App\Models\Role;
use Database\Seeders\PermissionSeeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Seed inicial de roles y permisos
        $this->call([
            PermissionSeeder::class,
        ]);

        // 2. Crear usuarios
        $admin = User::factory()->create([
            'name' => 'Geatel',
            'email' => 'admin@gmail.com',
            'password' => bcrypt('test'),
        ]);

        $fernando = User::factory()->create([
            'name' => 'Fernando',
            'email' => 'fernandoxl01xs@gmail.com',
            'password' => bcrypt('ignxts'),
        ]);

        $webie = User::factory()->create([
            'name' => 'Webie',
            'email' => 'webie@gmail.com',
            'password' => bcrypt('test'),
        ]);

        // 3. Asignar roles a usuarios
        $rolAdmin = Role::where('name', 'admin')->first();
        $rolWebie = Role::where('name', 'webie')->first();

        if ($rolAdmin) {
            $admin->assignRole($rolAdmin);
            $fernando->assignRole($rolAdmin);
        }

        if ($rolWebie) {
            $webie->assignRole($rolWebie);
        }

        // 4. Crear carteras
        $carterasData = [
            ['id' => 3,  'nombre' => 'Win',           'descripcion' => 'Cartera Servicios WIN',        'orden' => 1],
            ['id' => 29, 'nombre' => 'Win CROSS',     'descripcion' => 'Cartera Servicios WIN',        'orden' => 2],
            ['id' => 4,  'nombre' => 'PerúFibra',     'descripcion' => 'Cartera Servicios PerúFibra',  'orden' => 3],
            ['id' => 28, 'nombre' => 'Cable Perú',    'descripcion' => 'Cartera Servicios CP',         'orden' => 4],
            ['id' => 1,  'nombre' => 'Telefonía',     'descripcion' => 'Cartera Telefonía España',     'orden' => 5],
            ['id' => 2,  'nombre' => 'Energía',       'descripcion' => 'Cartera Energía España',       'orden' => 6],
            ['id' => 27, 'nombre' => 'Fidelización',  'descripcion' => 'Seguimiento Fidelización Energía', 'orden' => 7],
            ['id' => 30, 'nombre' => 'Infinite',      'descripcion' => 'Agencia de Viajes',            'orden' => 8],
        ];

        $carteras = [];
        foreach ($carterasData as $data) {
            $carteras[$data['nombre']] = Cartera::updateOrCreate(['id' => $data['id']], $data);
        }

        $reportesData = [
            ['cartera' => 'Energía',    'nombre' => 'Conexion_Agentes', 'link' => 'https://app.powerbi.com/view?r=eyJrIjoiYTUzNTdiNzEtYjQ3OS00ZGZlLWI5YjktODFiNzU1NDBkZmQ4IiwidCI6ImFjMGFmY2JlLTEzYzAtNDIyYi1iZGJhLTA0ZDk5ZmJlMzljZCIsImMiOjR9', 'orden' => 3],
            ['cartera' => 'Telefonía',  'nombre' => 'Conexion_Agentes', 'link' => 'https://app.powerbi.com/view?r=eyJrIjoiMGY1YjA0MjktN2JmNC00MTM5LWIzM2ItZDdmZmM2ZWE5Mjc0IiwidCI6ImFjMGFmY2JlLTEzYzAtNDIyYi1iZGJhLTA0ZDk5ZmJlMzljZCIsImMiOjR9', 'orden' => 3],
            ['cartera' => 'Telefonía',  'nombre' => 'Indicadores por Día', 'link' => 'https://app.powerbi.com/view?r=eyJrIjoiN2JiMDYxMDktZmU4NS00NmYyLTk1OTAtMmE1NmE0YTkwNjI1IiwidCI6ImFjMGFmY2JlLTEzYzAtNDIyYi1iZGJhLTA0ZDk5ZmJlMzljZCIsImMiOjR9', 'orden' => 1],
            ['cartera' => 'Win',        'nombre' => 'Indicadores por Día', 'link' => 'https://app.powerbi.com/view?r=eyJrIjoiMmIyZmQ5MzEtMDIxOS00NjgxLTliOWMtMWQ1ZTI5YjExZThhIiwidCI6ImFjMGFmY2JlLTEzYzAtNDIyYi1iZGJhLTA0ZDk5ZmJlMzljZCIsImMiOjR9', 'orden' => 1],
            ['cartera' => 'Win',        'nombre' => 'Conexion_Agentes', 'link' => 'https://app.powerbi.com/view?r=eyJrIjoiNmIzY2FkNGQtNDA5OC00NTM1LTk5NDItOTRiNWM1OWQyYzE0IiwidCI6ImFjMGFmY2JlLTEzYzAtNDIyYi1iZGJhLTA0ZDk5ZmJlMzljZCIsImMiOjR9', 'orden' => 7],
            ['cartera' => 'Energía',    'nombre' => 'Indicadores por Día', 'link' => 'https://app.powerbi.com/view?r=eyJrIjoiYjI4YjU5ODMtM2RkYy00OTU3LTg3MWMtMjdjNDc0YzU5OTA0IiwidCI6ImFjMGFmY2JlLTEzYzAtNDIyYi1iZGJhLTA0ZDk5ZmJlMzljZCIsImMiOjR9', 'orden' => 1],
            ['cartera' => 'Telefonía',  'nombre' => 'Seguimiento Ventas', 'link' => 'https://app.powerbi.com/view?r=eyJrIjoiZGYwZmJjZWMtOTUxNi00Y2Y3LThkM2QtZGI0MmY5ODZjYjg0IiwidCI6ImFjMGFmY2JlLTEzYzAtNDIyYi1iZGJhLTA0ZDk5ZmJlMzljZCIsImMiOjR9', 'orden' => 2],
            ['cartera' => 'Energía',    'nombre' => 'Seguimiento Ventas', 'link' => 'https://app.powerbi.com/view?r=eyJrIjoiZTcxNmViYTYtN2Q5Zi00NTFiLTk1YjItOWRlYTczOGI0MDI5IiwidCI6ImFjMGFmY2JlLTEzYzAtNDIyYi1iZGJhLTA0ZDk5ZmJlMzljZCIsImMiOjR9', 'orden' => 2],
            ['cartera' => 'PerúFibra',  'nombre' => 'Indicadores_BD', 'link' => 'https://app.powerbi.com/view?r=eyJrIjoiMWNiMGE1Y2QtNjY2OC00MGQwLTlkMWMtNzEwZmY4ZjNhZmNmIiwidCI6ImFjMGFmY2JlLTEzYzAtNDIyYi1iZGJhLTA0ZDk5ZmJlMzljZCIsImMiOjR9', 'orden' => 4],
            ['cartera' => 'PerúFibra',  'nombre' => 'Perú_Fibra_Día', 'link' => 'https://app.powerbi.com/view?r=eyJrIjoiN2Y0NjY0M2ItZTc0OC00MGQ1LWJjODAtMGZjZWRhNGM2YjBiIiwidCI6ImFjMGFmY2JlLTEzYzAtNDIyYi1iZGJhLTA0ZDk5ZmJlMzljZCIsImMiOjR9', 'orden' => 1],
            ['cartera' => 'Fidelización', 'nombre' => 'Conexion_Agentes', 'link' => 'https://app.powerbi.com/view?r=eyJrIjoiNzc0M2MyZTMtOWUxNS00OTM1LWE2MDgtYjU0MzljODJhYzBhIiwidCI6ImFjMGFmY2JlLTEzYzAtNDIyYi1iZGJhLTA0ZDk5ZmJlMzljZCIsImMiOjR9', 'orden' => 1],
            ['cartera' => 'Cable Perú',  'nombre' => 'Indicadores_BD', 'link' => 'https://app.powerbi.com/view?r=eyJrIjoiYjEyNzJlYTItOTAwZi00NWM2LTg3ZGUtNWQ4MzMzNDE4ZDI4IiwidCI6ImFjMGFmY2JlLTEzYzAtNDIyYi1iZGJhLTA0ZDk5ZmJlMzljZCIsImMiOjR9', 'orden' => 1],
            ['cartera' => 'Cable Perú',  'nombre' => 'Seguimiento', 'link' => 'https://app.powerbi.com/view?r=eyJrIjoiZTBhOWM5YTQtY2UyMi00NDY4LTk1OTYtNWIyMzAwNDM3NjI2IiwidCI6ImFjMGFmY2JlLTEzYzAtNDIyYi1iZGJhLTA0ZDk5ZmJlMzljZCIsImMiOjR9', 'orden' => 2],
            ['cartera' => 'PerúFibra',  'nombre' => 'Conexion_Agentes', 'link' => 'https://app.powerbi.com/view?r=eyJrIjoiNjliM2RkNzgtMDA1Ni00ZjNmLTg2YTQtZDNjYWIxNGM4M2E2IiwidCI6ImFjMGFmY2JlLTEzYzAtNDIyYi1iZGJhLTA0ZDk5ZmJlMzljZCIsImMiOjR9', 'orden' => 3],
            ['cartera' => 'PerúFibra',  'nombre' => 'Seguimiento', 'link' => 'https://app.powerbi.com/view?r=eyJrIjoiNmI2NjRiMjQtZmRiMC00MmU2LTgwNjctMjNmNDhmODVhNzdmIiwidCI6ImFjMGFmY2JlLTEzYzAtNDIyYi1iZGJhLTA0ZDk5ZmJlMzljZCIsImMiOjR9', 'orden' => 2],
            ['cartera' => 'Win',        'nombre' => 'Indicadores_BD_CROSS_Dia', 'link' => 'https://app.powerbi.com/view?r=eyJrIjoiZWQ3ZWZkNzgtNzI3My00MWU1LWE0NDYtMjg4MGQ0MGZlNGU3IiwidCI6ImFjMGFmY2JlLTEzYzAtNDIyYi1iZGJhLTA0ZDk5ZmJlMzljZCIsImMiOjR9', 'orden' => 3],
            ['cartera' => 'Cable Perú',  'nombre' => 'Conexion_Agentes', 'link' => 'https://app.powerbi.com/view?r=eyJrIjoiNjU0YWI3NjAtOTEzOS00ODQyLThiODItOGRhZGQyMTc4ZmE1IiwidCI6ImFjMGFmY2JlLTEzYzAtNDIyYi1iZGJhLTA0ZDk5ZmJlMzljZCIsImMiOjR9', 'orden' => 3],
            ['cartera' => 'Energía',    'nombre' => 'Indicadores BD Histórico', 'link' => 'https://app.powerbi.com/view?r=eyJrIjoiZTg1YmMxYzQtOTNmZC00YzVmLThmMDktMzFlY2M3YjAxNjM4IiwidCI6ImFjMGFmY2JlLTEzYzAtNDIyYi1iZGJhLTA0ZDk5ZmJlMzljZCIsImMiOjR9', 'orden' => 5],
            ['cartera' => 'Energía',    'nombre' => 'Indicadores_BD_Día', 'link' => 'https://app.powerbi.com/view?r=eyJrIjoiOGEwMzFiZmItNjY2Zi00ODRlLWJhNzAtNDI2MWMwMmI0NjU0IiwidCI6ImFjMGFmY2JlLTEzYzAtNDIyYi1iZGJhLTA0ZDk5ZmJlMzljZCIsImMiOjR9', 'orden' => 4],
            ['cartera' => 'Win',        'nombre' => 'Indicadores_BD_CROSS_Historico', 'link' => 'https://app.powerbi.com/view?r=eyJrIjoiZjEzMzNjYmEtNDA0YS00ZjJlLWFiODctOWYwMjMzOWRlMGFmIiwidCI6ImFjMGFmY2JlLTEzYzAtNDIyYi1iZGJhLTA0ZDk5ZmJlMzljZCIsImMiOjR9', 'orden' => 3],
            ['cartera' => 'Win',        'nombre' => 'Indicadores_BD_OUT_Dia', 'link' => 'https://app.powerbi.com/view?r=eyJrIjoiMThlOTgxYWEtMDAwNy00ZjQyLTgwOTAtMjMyOWFiZmQzZjUxIiwidCI6ImFjMGFmY2JlLTEzYzAtNDIyYi1iZGJhLTA0ZDk5ZmJlMzljZCIsImMiOjR9', 'orden' => 4],
            ['cartera' => 'Win',        'nombre' => 'Indicadores_BD_OUT_Historico', 'link' => 'https://app.powerbi.com/view?r=eyJrIjoiMWM2ZjQ1OTMtYTk3Mi00NzMyLWJjZmUtOGQ5NjhhYTY0ZTIyIiwidCI6ImFjMGFmY2JlLTEzYzAtNDIyYi1iZGJhLTA0ZDk5ZmJlMzljZCIsImMiOjR9', 'orden' => 5],
            ['cartera' => 'Win',        'nombre' => 'Seguimiento Ventas', 'link' => 'https://app.powerbi.com/view?r=eyJrIjoiNmRkNDk4MmItMWY5Zi00Mjk0LWExNjUtZmJlNTA2MGQwZGIyIiwidCI6ImFjMGFmY2JlLTEzYzAtNDIyYi1iZGJhLTA0ZDk5ZmJlMzljZCIsImMiOjR9', 'orden' => 2],
            ['cartera' => 'Infinite',   'nombre' => 'Indicadores BD del Día', 'link' => 'https://app.powerbi.com/view?r=eyJrIjoiYTY2MGUwNmQtYzMxZC00MDRmLTg5ZjctYTE4NDMzNjllNzM4IiwidCI6ImFjMGFmY2JlLTEzYzAtNDIyYi1iZGJhLTA0ZDk5ZmJlMzljZCIsImMiOjR9', 'orden' => 2],
            ['cartera' => 'Infinite',   'nombre' => 'Indicadores_BD_Acumulado', 'link' => 'https://app.powerbi.com/view?r=eyJrIjoiNWZjNDdhNmItZDMxNS00MTk2LWJjOTgtMDIxZTc0YTJlMjU2IiwidCI6ImFjMGFmY2JlLTEzYzAtNDIyYi1iZGJhLTA0ZDk5ZmJlMzljZCIsImMiOjR9', 'orden' => 3],
            ['cartera' => 'Infinite',   'nombre' => 'Indicadores de Venta en el Día', 'link' => 'https://app.powerbi.com/view?r=eyJrIjoiZWRjNzQ2YmYtYjBiZC00OWM2LWIwMWItNjk5NjljNGVlNTNjIiwidCI6ImFjMGFmY2JlLTEzYzAtNDIyYi1iZGJhLTA0ZDk5ZmJlMzljZCIsImMiOjR9', 'orden' => 1],
            ['cartera' => 'Telefonía',  'nombre' => 'BD_Indicadores_Mes', 'link' => 'https://app.powerbi.com/view?r=eyJrIjoiYmNjMjViOTktNGY0OC00ZTUxLWJhMGUtZDNiMWU1NGU0MTVhIiwidCI6ImFjMGFmY2JlLTEzYzAtNDIyYi1iZGJhLTA0ZDk5ZmJlMzljZCIsImMiOjR9', 'orden' => 4],
        ];

        foreach ($reportesData as $data) {
            if (isset($carteras[$data['cartera']])) {
                \App\Models\Reporte::create([
                    'nombre'      => $data['nombre'],
                    'link'        => $data['link'],
                    'orden'       => $data['orden'],
                    'cartera_id'  => $carteras[$data['cartera']]->id,
                ]);
            }
        }

        foreach ($reportesData as $data) {
            if (isset($carteras[$data['cartera']])) {
                Reporte::create([
                    'nombre'     => $data['nombre'],
                    'link'       => $data['link'],
                    'orden'      => $data['orden'],
                    'cartera_id' => $carteras[$data['cartera']]->id,
                ]);
            }
        }

        // 6. Asignar carteras y reportes a los roles
        $allCarteras = Cartera::pluck('id')->toArray();
        $allReportes = Reporte::pluck('id')->toArray();

        if ($rolAdmin) {
            $rolAdmin->carteras()->sync($allCarteras);
            $rolAdmin->reportes()->sync($allReportes);
        }

        if ($rolWebie) {
            $rolWebie->carteras()->sync($allCarteras);
            $rolWebie->reportes()->sync($allReportes);
        }
    }
}
