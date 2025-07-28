<?php

namespace App\Jobs;

use App\Models\Vodafone;
use App\Models\LogImportacionVodafone;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class VodafoneImportJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected $datos;
    protected $modo;
    protected $logId;
    protected $userId;

    public function __construct($datos, $modo, $logId, $userId)
    {
        $this->datos = $datos;
        $this->modo = $modo;
        $this->logId = $logId;
        $this->userId = $userId;
    }

    public function handle()
    {
        $errores = [];
        $user = User::find($this->userId);
        $log = LogImportacionVodafone::find($this->logId);

        $modo = strtolower(trim($this->modo ?? ''));
        Log::info("Modo de importación recibido en Job", ['modo' => $modo]);

        if ($log) {
            $log->estado = 'procesando'; // 🔥 ESTADO EN PROCESO
            $log->save();
        }

        DB::beginTransaction();
        try {
            foreach ($this->datos as $row) {
                $dni = trim($row['dni_cliente'] ?? '');

                if (!$dni) continue;

                $data = [
                    'dni_cliente' => $dni,
                    'telefono_principal' => $row['telefono_principal'] ?? null,
                    'nombre_cliente' => $row['nombre_cliente'] ?? null,
                    'orden_trabajo_anterior' => $row['orden_trabajo_anterior'] ?? null,
                    'telefono_adicional' => $row['telefono_adicional'] ?? null,
                    'correo_referencia' => $row['correo_referencia'] ?? null,
                    'direccion_historico' => $row['direccion_historico'] ?? null,
                    'observaciones' => $row['observaciones'] ?? null,
                    'marca_base' => $row['marca_base'] ?? null,
                    'origen_motivo_cancelacion' => $row['origen_motivo_cancelacion'] ?? null,
                    'trazabilidad' => 'pendiente',
                    'asignado_a_id' => $row['asignado_a_id'] ?? null,
                    'user_id' => $this->userId,
                    'upload_id' => $row['upload_id'] ?? null,
                ];

                try {
                    $dniStr = (string) $dni;
                    Log::info("Procesando fila", ['fila' => $row['index'], 'dni' => $dniStr]);

                    $existente = Vodafone::withTrashed()
                        ->whereRaw('CAST(dni_cliente AS CHAR) = ?', [$dniStr])
                        ->first();

                    if ($existente) {
                        Log::info("Registro encontrado", [
                            'dni' => $dniStr,
                            'id' => $existente->id,
                            'eliminado_logicamente' => $existente->trashed()
                        ]);

                        if ($modo === 'actualizar') {
                            if ($existente->trashed()) {
                                Log::info("Restaurando registro", ['dni' => $dniStr]);
                                $existente->restore();
                            }
                            $existente->update($data);
                            Log::info("Registro actualizado", ['dni' => $dniStr]);
                        } elseif ($modo === 'omitir') {
                            Log::info("Registro omitido por configuración", ['dni' => $dniStr]);
                        } else {
                            Log::info("Eliminando físicamente registro existente", ['dni' => $dniStr]);
                            $existente->forceDelete();
                            $nuevo = Vodafone::create($data);
                            Log::info("Registro recreado tras eliminación", ['dni' => $dniStr, 'nuevo_id' => $nuevo->id]);
                        }
                    } else {
                        Log::info("No se encontró registro existente. Creando nuevo", ['dni' => $dniStr]);
                        $nuevo = Vodafone::create($data);
                        Log::info("Registro creado exitosamente", ['dni' => $dniStr, 'nuevo_id' => $nuevo->id]);
                    }
                } catch (\Throwable $e) {
                    $errores[] = "Fila {$row['index']}: " . $e->getMessage();
                    Log::error("Error en fila {$row['index']}: " . $e->getMessage());
                }
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            $errores[] = "Error general: " . $e->getMessage();
            Log::error('Error en importación masiva Vodafone: ' . $e->getMessage());
        }

        if ($log) {
            $log->estado = 'finalizado'; // 🏁 FINALIZADO
            $log->errores_json = json_encode($errores);
            $log->save();
        }
    }
}
