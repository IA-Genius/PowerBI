<?php

namespace App\Jobs;

use App\Models\Vodafone;
use App\Models\VodafoneAuditoria;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class AgendarVodafoneJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $registros = Vodafone::where('trazabilidad', 'asignado')
            ->where(function ($q) {
                $q->whereNull('marca_base')
                    ->orWhereNull('origen_motivo_cancelacion')
                    ->orWhereNull('nombre_cliente')
                    ->orWhereNull('dni_cliente')
                    ->orWhereNull('telefono_principal')
                    ->orWhere('marca_base', '')
                    ->orWhere('origen_motivo_cancelacion', '')
                    ->orWhere('nombre_cliente', '')
                    ->orWhere('dni_cliente', '')
                    ->orWhere('telefono_principal', '');
            })
            ->get();

        Log::info('AgendarVodafoneJob: Registros encontrados', [
            'ids' => $registros->pluck('id')->toArray(),
            'cantidad' => $registros->count(),
        ]);

        foreach ($registros as $registro) {
            $registro->update(['trazabilidad' => 'agendado']);
            VodafoneAuditoria::create([
                'vodafone_id' => $registro->id,
                'user_id' => $registro->asignado_a_id,
                'accion' => 'agendado',
                'campos_editados' => null,
                'fecha' => now(),
            ]);
            Log::info('AgendarVodafoneJob: Registro agendado', [
                'id' => $registro->id,
                'asignado_a_id' => $registro->asignado_a_id,
            ]);
        }
    }
}
