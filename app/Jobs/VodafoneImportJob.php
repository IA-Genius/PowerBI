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

        DB::beginTransaction();
        try {
            foreach ($this->datos as $row) {
                $dni = $row['dni_cliente'] ?? null;
                $tel = $row['telefono_principal'] ?? null;

                $existente = Vodafone::where('dni_cliente', $dni)
                    ->orWhere('telefono_principal', $tel)
                    ->first();

                try {
                    if ($existente && $this->modo === 'omitir') {
                        $errores[] = "Fila {$row['index']}: Duplicado omitido (DNI $dni)";
                        Log::info("Fila {$row['index']}: Duplicado omitido (DNI $dni)");
                        continue;
                    }

                    $data = [
                        'nombre_cliente' => $row['nombre_cliente'] ?? '',
                        'dni_cliente' => $row['dni_cliente'] ?? '',
                        'telefono_principal' => $row['telefono_principal'] ?? '',
                        'telefono_adicional' => $row['telefono_adicional'] ?? '',
                        'correo_referencia' => $row['correo_referencia'] ?? '',
                        'direccion_historico' => $row['direccion_historico'] ?? '',
                        'marca_base' => $row['marca_base'] ?? '',
                        'origen_motivo_cancelacion' => $row['origen_motivo_cancelacion'] ?? '',
                        'orden_trabajo_anterior' => $row['orden_trabajo_anterior'] ?? '',
                        'observaciones' => $row['observaciones'] ?? '',
                        'user_id' => $user->id,
                        'upload_id' => $log->id,
                        'trazabilidad' => 'pendiente',
                    ];

                    Log::info("Intentando crear/actualizar registro Vodafone", $data);

                    if ($existente && $this->modo === 'actualizar') {
                        $existente->update($data);
                        Log::info("Registro actualizado", ['id' => $existente->id]);
                    } else {
                        $nuevo = Vodafone::create($data);
                        Log::info("Registro creado", ['id' => $nuevo->id]);
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
        // GUARDA EL LOG SIEMPRE DESPUÉS DEL COMMIT O ROLLBACK
        if ($log) {
            $log->errores_json = json_encode($errores);
            $log->save();
        }
    }
}
