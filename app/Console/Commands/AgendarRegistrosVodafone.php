<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Vodafone;
use App\Models\VodafoneAuditoria;
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

        Log::info('AgendarRegistrosVodafone: Registros encontrados', [
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
            Log::info('AgendarRegistrosVodafone: Registro agendado', [
                'id' => $registro->id,
                'asignado_a_id' => $registro->asignado_a_id,
            ]);
        }

        $this->info("Registros agendados: " . $registros->count());
    }
}
