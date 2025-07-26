<?php

namespace App\Imports;

use App\Models\Vodafone;
use App\Models\LogImportacionVodafone;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;

class VodafoneImport implements ToCollection
{
    public function collection(Collection $rows)
    {
        $user = Auth::user();

        Log::debug('📥 Iniciando importación Vodafone', [
            'user_id' => $user->id,
            'total_filas' => $rows->count(),
        ]);

        $upload = LogImportacionVodafone::create([
            'user_id' => $user->id,
            'nombre_archivo' => 'Import manual',
            'cantidad_registros' => $rows->count() - 1,
        ]);

        $errores = [];

        foreach ($rows as $index => $row) {
            if ($index === 0) continue;

            $dni = $row[3] ?? null;
            $telefono = $row[5] ?? null;

            $existe = Vodafone::where('dni_cliente', $dni)
                ->orWhere('telefono_principal', $telefono)
                ->exists();

            if ($existe) {
                Log::warning("⚠️ Registro duplicado en fila {$index}", [
                    'dni_cliente' => $dni,
                    'telefono_principal' => $telefono,
                ]);
                $errores[] = "Fila {$index}: Duplicado (DNI: {$dni}, Tel: {$telefono})";
                continue;
            }

            try {
                Vodafone::create([
                    'user_id' => $user->id,
                    'upload_id' => $upload->id,
                    'trazabilidad' => 'pendiente',
                    'marca_base' => $row[0] ?? null,
                    'origen_motivo_cancelacion' => $row[1] ?? null,
                    'nombre_cliente' => $row[2] ?? null,
                    'dni_cliente' => $dni,
                    'orden_trabajo_anterior' => $row[4] ?? null,
                    'telefono_principal' => $telefono,
                    'telefono_adicional' => $row[6] ?? null,
                    'correo_referencia' => $row[7] ?? null,
                    'direccion_historico' => $row[8] ?? null,
                    'observaciones' => $row[9] ?? null,
                ]);

                Log::debug("✅ Fila importada (index {$index})", [
                    'dni_cliente' => $dni,
                    'nombre_cliente' => $row[2] ?? null,
                ]);
            } catch (\Throwable $e) {
                Log::error("❌ Error en fila {$index}", [
                    'row' => $row,
                    'error' => $e->getMessage(),
                ]);
                $errores[] = "Fila {$index}: Error - " . $e->getMessage();
            }
        }

        if (count($errores)) {
            $upload->errores_json = $errores;
            $upload->save();
        }

        Log::debug('🏁 Importación finalizada', [
            'importados' => $rows->count() - 1 - count($errores),
            'con_errores' => count($errores),
        ]);
    }


    protected function parseDate($value)
    {
        if (!$value) return null;

        try {
            return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value);
        } catch (\Throwable $e) {
            Log::warning('⚠️ Error parseando fecha', [
                'value' => $value,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }
}
