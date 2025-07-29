<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Vodafone;
use App\Models\LogImportacionVodafone;

use App\Jobs\VodafoneImportJob;
use Illuminate\Support\Facades\Log;

class VodafoneImportController extends Controller
{
    public function preview(Request $request)
    {
        $request->validate([
            'archivo' => 'required|file|mimes:xlsx,xls',
        ]);

        try {
            $array = \Maatwebsite\Excel\Facades\Excel::toArray([], $request->file('archivo'));

            $dnilist = Vodafone::pluck('dni_cliente')->toArray();
            $tellist = Vodafone::pluck('telefono_principal')->toArray();

            $rows = collect($array[0] ?? [])->slice(1)
                ->map(function ($row, $index) use ($dnilist, $tellist) {
                    $dni = $row[3] ?? '';
                    $tel = $row[5] ?? '';
                    return [
                        'index' => $index + 2,
                        'id_interno' => $row[0] ?? '',
                        'fecha_registro' => $row[1] ?? '',
                        'nombre_cliente' => $row[2] ?? '',
                        'dni_cliente' => $dni,
                        'direccion_cliente' => $row[4] ?? '',
                        'telefono_principal' => $tel,
                        'correo_electronico' => $row[6] ?? '',
                        'operador_origen' => $row[7] ?? '',
                        'operador_destino' => $row[8] ?? '',
                        'duplicado' => ($dni && in_array($dni, $dnilist)) || ($tel && in_array($tel, $tellist)),
                    ];
                })
                ->filter(function ($row) {
                    // Excluir registros donde todos los campos principales estén vacíos
                    return $row['dni_cliente'] || $row['telefono_principal'] || $row['nombre_cliente'];
                });

            $maxPreview = 1000;
            $truncado = $rows->count() > $maxPreview;
            $previewRows = $rows->take($maxPreview)->values()->toArray();

            // Calcular totales reales
            $total_registros = $rows->count();
            $total_duplicados = $rows->filter(function ($row) {
                return $row['duplicado'];
            })->count();
            $total_nuevos = $rows->filter(function ($row) {
                return !$row['duplicado'];
            })->count();

            return response()->json([
                'preview' => $previewRows,
                'truncado' => $truncado,
                'total' => $total_registros,
                'total_registros' => $total_registros,
                'total_duplicados' => $total_duplicados,
                'total_nuevos' => $total_nuevos,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error leyendo archivo: ' . $e->getMessage(),
            ], 422);
        }
    }


    public function importarConfirmado(Request $request)
    {
        $request->validate([
            'datos' => 'required|array',
            'modo' => 'nullable|string|in:omitir,actualizar',
        ]);

        $user = $request->user();

        // Asegurarse que los datos sean un array plano
        $datos = array_values($request->datos);

        // Validar que cada registro tenga los campos mínimos
        $datos = array_filter($datos, function ($row) {
            return isset($row['dni_cliente']) && isset($row['telefono_principal']);
        });

        $log = LogImportacionVodafone::create([
            'user_id' => $user->id,
            'nombre_archivo' => 'importacion-confirmada',
            'cantidad_registros' => count($datos),
        ]);

        Log::info('Importando registros', [
            'cantidad' => count($datos),
            'primer_registro' => $datos[0] ?? null,
        ]);

        // Lanzar el job en background
        dispatch(new VodafoneImportJob($datos, $request->modo, $log->id, $user->id));

        return response()->json([
            'message' => 'Importación en proceso',
            'log_id' => $log->id,
        ]);
    }

    public function obtenerErroresLog($id)
    {
        $log = LogImportacionVodafone::findOrFail($id);

        return response()->json([
            'estado' => $log->estado,
            'errores' => json_decode($log->errores_json ?? '[]'),
        ]);
    }
}
