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

        // Crear log de importación
        $upload = LogImportacionVodafone::create([
            'user_id' => $user->id,
            'nombre_archivo' => 'Import manual',
            'cantidad_registros' => $rows->count() - 1,
        ]);

        Log::debug('🗂 Log de importación creado', [
            'upload_id' => $upload->id,
        ]);

        foreach ($rows as $index => $row) {
            if ($index === 0) {
                Log::debug('🔼 Encabezado detectado (fila 0), saltando');
                continue;
            }

            try {
                $registro = Vodafone::create([
                    'user_id' => $user->id,
                    'upload_id' => $upload->id,
                    'trazabilidad' => 'pendiente',
                    'marca_base' => $row[0] ?? null,
                    'origen_motivo_cancelacion' => $row[1] ?? null,
                    'nombre_cliente' => $row[2] ?? null,
                    'dni_cliente' => $row[3] ?? null,
                    'orden_trabajo_anterior' => $row[4] ?? null,
                    'telefono_principal' => $row[5] ?? null,
                    'telefono_adicional' => $row[6] ?? null,
                    'correo_referencia' => $row[7] ?? null,
                    'direccion_historico' => $row[8] ?? null,
                    'observaciones' => $row[9] ?? null,
                    // Si tienes asignado_a_id en el archivo, puedes agregarlo aquí:
                    // 'asignado_a_id' => $row[10] ?? null,
                ]);

                Log::debug("✅ Fila importada (index {$index})", [
                    'id' => $registro->id,
                    'dni_cliente' => $registro->dni_cliente,
                    'nombre_cliente' => $registro->nombre_cliente,
                ]);
            } catch (\Throwable $e) {
                Log::error("❌ Error importando fila {$index}", [
                    'row_data' => $row,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::debug('🏁 Importación finalizada correctamente', [
            'total_importados' => $rows->count() - 1,
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
