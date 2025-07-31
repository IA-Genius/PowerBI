<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Vodafone;
use App\Models\VodafoneAuditoria;
use App\Models\VodafoneAsignacionHistorial;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class RetornarRegistrosVodafone extends Command
{
    // =======================
    // 1. PROPIEDADES DEL COMANDO
    // =======================

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vodafone:retornar-incompletos';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Procesa registros asignados/completados: evalúa campos y desasigna usuarios';

    // =======================
    // 2. CONSTANTES Y CONFIGURACIÓN
    // =======================

    private const ESTADO_ASIGNADO = 'asignado';
    private const ESTADO_COMPLETADO = 'completado';
    private const ESTADO_RETORNADO = 'retornado';

    private const CAMPOS_REQUERIDOS = [
        'orden_trabajo_anterior',
        'origen_base',
        'nombre_cliente',
        'dni_cliente',
        'telefono_principal',
        'telefono_adicional',
        'correo_referencia',
        'direccion_historico',
        'marca_base',
        'origen_motivo_cancelacion',
        'observaciones'
    ];

    // =======================
    // 3. MÉTODO PRINCIPAL
    // =======================

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('¡Comando ejecutado por el scheduler!');

        // Debug: Mostrar todos los estados disponibles
        $this->mostrarEstadisticasGenerales();

        $registros = $this->obtenerRegistrosAsignados();
        $estadisticas = $this->procesarRegistros($registros);

        $this->mostrarResultados($estadisticas);
    }

    // =======================
    // 4. MÉTODOS DE OBTENCIÓN DE DATOS
    // =======================

    /**
     * Obtiene todos los registros que necesitan procesamiento
     */
    private function obtenerRegistrosAsignados()
    {
        $hoy = Carbon::today()->startOfDay();

        Log::info('RetornarRegistrosVodafone: Fecha límite', ['fecha' => $hoy]);

        // Buscar registros 'asignado' Y registros 'completado' que tengan asignado_a_id
        $registrosAsignados = Vodafone::where('trazabilidad', self::ESTADO_ASIGNADO)->get();
        $registrosCompletadosConAsignacion = Vodafone::where('trazabilidad', self::ESTADO_COMPLETADO)
            ->whereNotNull('asignado_a_id')
            ->get();

        // Combinar ambas colecciones
        $registros = $registrosAsignados->merge($registrosCompletadosConAsignacion);

        $this->info("🔍 Registros encontrados:");
        $this->info("   📋 Estado 'asignado': " . $registrosAsignados->count());
        $this->info("   ✅ Estado 'completado' con asignación: " . $registrosCompletadosConAsignacion->count());
        $this->info("   📊 Total a procesar: " . $registros->count());

        if ($registros->count() === 0) {
            $this->warn("⚠️  No hay registros para procesar");
        }

        return $registros;
    }

    // =======================
    // 5. MÉTODOS DE PROCESAMIENTO
    // =======================

    /**
     * Procesa todos los registros encontrados
     */
    private function procesarRegistros($registros)
    {
        $estadisticas = [
            'retornados' => 0,
            'completados' => 0,
            'desasignados_completados' => 0
        ];

        foreach ($registros as $registro) {
            $resultado = $this->procesarRegistroIndividual($registro);

            if ($resultado['cambio']) {
                $estadisticas[$resultado['tipo']]++;
            }
        }

        return $estadisticas;
    }

    /**
     * Procesa un registro individual
     */
    private function procesarRegistroIndividual($registro)
    {
        $estadoAnterior = $registro->trazabilidad;
        $asignadoDe = $registro->asignado_a_id;

        // Si ya está completado, solo desasignar
        if ($estadoAnterior === self::ESTADO_COMPLETADO) {
            if ($asignadoDe) {
                $this->actualizarRegistro($registro, self::ESTADO_COMPLETADO);
                $this->registrarCambio($registro, $asignadoDe, self::ESTADO_COMPLETADO);

                return [
                    'cambio' => true,
                    'tipo' => 'desasignados_completados'
                ];
            }
            return ['cambio' => false];
        }

        // Para registros 'asignado', evaluar campos y cambiar estado
        $camposCompletos = $this->verificarCamposCompletos($registro);
        $nuevoEstado = $camposCompletos ? self::ESTADO_COMPLETADO : self::ESTADO_RETORNADO;

        if ($estadoAnterior !== $nuevoEstado) {
            $this->actualizarRegistro($registro, $nuevoEstado);
            $this->registrarCambio($registro, $asignadoDe, $nuevoEstado);

            return [
                'cambio' => true,
                'tipo' => $nuevoEstado === self::ESTADO_COMPLETADO ? 'completados' : 'retornados'
            ];
        }

        return ['cambio' => false];
    }

    // =======================
    // 6. MÉTODOS DE VALIDACIÓN
    // =======================

    /**
     * Verifica si todos los campos requeridos están completos
     */
    private function verificarCamposCompletos($registro)
    {
        foreach (self::CAMPOS_REQUERIDOS as $campo) {
            if (empty($registro->$campo)) {
                return false;
            }
        }

        return true;
    }

    // =======================
    // 7. MÉTODOS DE ACTUALIZACIÓN
    // =======================

    /**
     * Actualiza el registro con el nuevo estado y desasigna
     * Aplica tanto para registros retornados como completados
     */
    private function actualizarRegistro($registro, $nuevoEstado)
    {
        $registro->update([
            'trazabilidad' => $nuevoEstado,
            'asignado_a_id' => null, // Desasignar TODOS los registros (retornados Y completados)
        ]);
    }

    // =======================
    // 8. MÉTODOS DE LOGGING
    // =======================

    /**
     * Registra el cambio en los logs
     */
    private function registrarCambio($registro, $asignadoDe, $nuevoEstado)
    {
        $accion = match ($nuevoEstado) {
            self::ESTADO_COMPLETADO => $registro->getOriginal('trazabilidad') === self::ESTADO_COMPLETADO
                ? 'Completado - solo desasignado'
                : 'Completado y desasignado',
            self::ESTADO_RETORNADO => 'Retornado y desasignado',
            default => 'Procesado y desasignado'
        };

        Log::info('RetornarRegistrosVodafone: Registro actualizado', [
            'id' => $registro->id,
            'asignado_de' => $asignadoDe,
            'estado_anterior' => $registro->getOriginal('trazabilidad'),
            'nuevo_estado' => $nuevoEstado,
            'desasignado' => true,
            'accion' => $accion,
        ]);
    }

    /**
     * Muestra los resultados del procesamiento
     */
    private function mostrarResultados($estadisticas)
    {
        $total = $estadisticas['retornados'] + $estadisticas['completados'] + $estadisticas['desasignados_completados'];

        if ($total === 0) {
            $this->info("✅ No hay registros que procesar en este momento");
            return;
        }

        $this->info(sprintf(
            "✅ Registros procesados: %d retornados + %d completados = %d total | TODOS desasignados",
            $estadisticas['retornados'],
            $estadisticas['completados'],
            $total
        ));

        if ($estadisticas['completados'] > 0) {
            $this->info("📋 Registros completados desasignados: {$estadisticas['completados']}");
        }

        if ($estadisticas['retornados'] > 0) {
            $this->info("� Registros retornados desasignados: {$estadisticas['retornados']}");
        }
    }

    /**
     * Muestra estadísticas generales para debugging
     */
    private function mostrarEstadisticasGenerales()
    {
        $totalRegistros = Vodafone::count();
        $estadisticas = Vodafone::selectRaw('trazabilidad, COUNT(*) as total')
            ->groupBy('trazabilidad')
            ->get()
            ->pluck('total', 'trazabilidad');

        $this->info("� ESTADÍSTICAS GENERALES:");
        $this->info("   Total de registros: $totalRegistros");

        foreach ($estadisticas as $estado => $cantidad) {
            $emoji = match ($estado) {
                'pendiente' => '⏳',
                'asignado' => '👤',
                'completado' => '✅',
                'retornado' => '🔄',
                'irrelevante' => '❌',
                default => '📄'
            };
            $this->info("   $emoji $estado: $cantidad");
        }

        $this->info(""); // Línea en blanco
    }
}
