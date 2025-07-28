<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Vodafone;
use App\Models\LogImportacionVodafone;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;
use App\Imports\VodafoneImport;

class VodafoneImportController extends Controller
{

    public function preview(Request $request)
    {
        $request->validate([
            'archivo' => 'required|file|mimes:xlsx,xls',
        ]);

        try {
            $array = Excel::toArray([], $request->file('archivo'));

            $rows = collect($array[0] ?? [])->slice(1)->map(function ($row, $index) {
                return [
                    'index' => $index + 2,
                    'id_interno' => $row[0] ?? '',
                    'fecha_registro' => $row[1] ?? '',
                    'nombre_cliente' => $row[2] ?? '',
                    'dni_cliente' => $row[3] ?? '',
                    'direccion_cliente' => $row[4] ?? '',
                    'telefono_principal' => $row[5] ?? '',
                    'correo_electronico' => $row[6] ?? '',
                    'operador_origen' => $row[7] ?? '',
                    'operador_destino' => $row[8] ?? '',
                    'duplicado' => Vodafone::where('dni_cliente', $row[3] ?? '')
                        ->orWhere('telefono_principal', $row[5] ?? '')
                        ->exists(),
                ];
            });

            return response()->json(['preview' => $rows]);
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
            'modo' => 'required|in:omitir,actualizar',
        ]);

        $modo = $request->modo;
        $user = $request->user();

        $log = LogImportacionVodafone::create([
            'user_id' => $user->id,
            'nombre_archivo' => 'importacion-confirmada',
            'cantidad_registros' => count($request->datos),
        ]);

        $errores = [];

        foreach ($request->datos as $row) {
            $this->guardarRegistroVodafone($row, $modo, $log, $user, $errores);
        }

        if ($errores) {
            Log::warning('⚠️ Errores durante la importación de Vodafone', [
                'log_id' => $log->id,
                'errores' => $errores,
                'usuario' => $user->name,
                'user_id' => $user->id,
            ]);
        }

        return response()->json([
            'message' => 'Importación completada correctamente',
        ]);
    }

    public function obtenerErroresLog($id)
    {
        $log = LogImportacionVodafone::findOrFail($id);

        return response()->json([
            'errores' => json_decode($log->errores_json ?? '[]'),
        ]);
    }

    /**
     * Función reutilizable para guardar o actualizar un registro de Vodafone
     */
    protected function guardarRegistroVodafone($row, $modo, $log, $user, &$errores)
    {
        $dni = $row['dni_cliente'] ?? null;
        $tel = $row['telefono_principal'] ?? null;

        $existente = Vodafone::where('dni_cliente', $dni)
            ->orWhere('telefono_principal', $tel)
            ->first();

        try {
            if ($existente && $modo === 'omitir') {
                $errores[] = "Fila {$row['index']}: Duplicado omitido (DNI $dni)";
                return;
            }

            $data = [
                'nombre_cliente' => $row['nombre_cliente'] ?? '',
                'telefono_principal' => $tel,
                'dni_cliente' => $dni,
                'direccion_cliente' => $row['direccion_cliente'] ?? '',
                'correo_electronico' => $row['correo_electronico'] ?? '',
                'operador_origen' => $row['operador_origen'] ?? '',
                'operador_destino' => $row['operador_destino'] ?? '',
                'fecha_registro' => $row['fecha_registro'] ?? null,
                'id_interno' => $row['id_interno'] ?? null,
                'upload_id' => $log->id,
            ];

            if ($existente && $modo === 'actualizar') {
                $existente->update($data);
            } else {
                Vodafone::create(array_merge($data, [
                    'user_id' => $user->id,
                    'trazabilidad' => 'pendiente',
                ]));
            }
        } catch (\Throwable $e) {
            $errores[] = "Fila {$row['index']}: " . $e->getMessage();
        }
    }
}
