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
                        'observaciones' => $row[9] ?? '', // <-- agrega esto según la posición real en tu Excel
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
        Log::info('Iniciando importación confirmada', [
            'modo' => $request->modo,
            'tiene_archivo' => $request->hasFile('archivo'),
        ]);

        $request->validate([
            'archivo' => 'required|file|mimes:xlsx,xls',
            'modo' => 'nullable|string|in:omitir,actualizar',
        ]);

        $user = $request->user();

        try {
            // Procesar TODOS los datos del archivo Excel (no solo el preview)
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
                        'observaciones' => $row[9] ?? '',
                        'duplicado' => ($dni && in_array($dni, $dnilist)) || ($tel && in_array($tel, $tellist)),
                    ];
                })
                ->filter(function ($row) {
                    // Excluir registros donde todos los campos principales estén vacíos
                    return $row['dni_cliente'] || $row['telefono_principal'] || $row['nombre_cliente'];
                });

            // Convertir a array plano para procesamiento
            $datos = $rows->toArray();

            // Validar que cada registro tenga los campos mínimos
            $datos = array_filter($datos, function ($row) {
                return isset($row['dni_cliente']) && isset($row['telefono_principal']);
            });

            // Mapeo de campos del array recibido a los campos esperados por Vodafone
            $mapeo = [
                'id_interno' => 'marca_base',
                'fecha_registro' => 'origen_motivo_cancelacion',
                'nombre_cliente' => 'nombre_cliente',
                'dni_cliente' => 'dni_cliente',
                'direccion_cliente' => 'orden_trabajo_anterior',
                'telefono_principal' => 'telefono_principal',
                'correo_electronico' => 'telefono_adicional',
                'operador_origen' => 'correo_referencia',
                'operador_destino' => 'direccion_historico',
                'observaciones' => 'observaciones',
            ];

            $datosMapeados = array_map(function ($row) use ($mapeo) {
                $nuevo = [];
                foreach ($mapeo as $origen => $destino) {
                    $nuevo[$destino] = $row[$origen] ?? null;
                }
                return $nuevo;
            }, $datos);

            $log = LogImportacionVodafone::create([
                'user_id' => $user->id,
                'nombre_archivo' => $request->file('archivo')->getClientOriginalName(),
                'cantidad_registros' => count($datosMapeados),
            ]);

            Log::info('Importando TODOS los registros del archivo', [
                'cantidad_total' => count($datosMapeados),
                'archivo' => $request->file('archivo')->getClientOriginalName(),
                'primer_registro' => $datosMapeados[0] ?? null,
            ]);

            // Lanzar el job en background con TODOS los datos mapeados
            dispatch(new VodafoneImportJob($datosMapeados, $request->modo, $log->id, $user->id));

            return response()->json([
                'message' => 'Importación en proceso',
                'log_id' => $log->id,
                'total_registros' => count($datosMapeados),
            ]);
        } catch (\Throwable $e) {
            Log::error('Error en importación confirmada: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Error procesando archivo: ' . $e->getMessage(),
            ], 422);
        }
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
