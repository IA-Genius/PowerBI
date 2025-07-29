<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Vodafone;
use App\Models\VodafoneAuditoria;
use App\Models\VodafoneAsignacionHistorial;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AgendarRegistrosVodafone extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vodafone:agendar-incompletos';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pasa registros asignados e incompletos a estado agendado y los registra en auditoría';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('¡Comando ejecutado por el scheduler!');

        $hoy = Carbon::today()->startOfDay();

        // Log para ver la fecha y la consulta
        Log::info('AgendarRegistrosVodafone: Fecha límite', ['fecha' => $hoy]);

        $registros = Vodafone::where('trazabilidad', 'asignado')->get();

        $agendados = 0;
        $completados = 0;
        foreach ($registros as $registro) {
            $estadoAnterior = $registro->trazabilidad;
            $asignadoDe = $registro->asignado_a_id;
            // Verificar si todos los campos requeridos están completos (no null y no vacío)
            $camposCompletos = (
                $registro->marca_base &&
                $registro->origen_motivo_cancelacion &&
                $registro->nombre_cliente &&
                $registro->dni_cliente &&
                $registro->orden_trabajo_anterior &&
                $registro->telefono_principal &&
                $registro->telefono_adicional &&
                $registro->correo_referencia &&
                $registro->direccion_historico &&
                $registro->observaciones
            );

            $nuevoEstado = $camposCompletos ? 'completado' : 'agendado';
            if ($estadoAnterior !== $nuevoEstado) {
                $registro->update(['trazabilidad' => $nuevoEstado]);
                if ($nuevoEstado === 'completado') {
                    $completados++;
                } else {
                    $agendados++;
                }
                // Solo log, sin crear auditoría
                Log::info('AgendarRegistrosVodafone: Registro actualizado', [
                    'id' => $registro->id,
                    'asignado_a_id' => $asignadoDe,
                    'nuevo_estado' => $nuevoEstado,
                ]);
            }
        }

        $this->info("Registros agendados: $agendados | completados: $completados");
    }
}
