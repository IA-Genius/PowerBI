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
                    'user_id'                    => $user->id,
                    'upload_id'                 => $upload->id,
                    'estado'                    => 'pendiente',

                    'dni_nif_cif'               => $row[0] ?? null,
                    'id_cliente'                => $row[1] ?? null,
                    'observacion_smart'         => $row[2] ?? null,
                    'oferta_comercial'          => $row[3] ?? null,
                    'operador_actual'           => $row[4] ?? null,
                    'telefono_contacto'         => $row[5] ?? null,
                    'nombre_cliente'            => $row[6] ?? null,
                    'direccion_instalacion'     => $row[7] ?? null,
                    'fecha_creacion'            => $this->parseDate($row[8] ?? null),
                    'fecha_cierre'              => $this->parseDate($row[9] ?? null),
                    'observaciones_back_office' => $row[10] ?? null,
                    'tipificaciones'            => $row[11] ?? null,
                    'observaciones_operaciones' => $row[12] ?? null,
                ]);

                Log::debug("✅ Fila importada (index {$index})", [
                    'id' => $registro->id,
                    'dni_nif_cif' => $registro->dni_nif_cif,
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
