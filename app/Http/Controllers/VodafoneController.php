<?php

namespace App\Http\Controllers;

use App\Models\Vodafone;
use App\Models\User;
use App\Models\VodafoneAsignacion;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

use Carbon\Carbon;

class VodafoneController extends Controller
{

    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Fechas por defecto: ayer y hoy
        $fechaDesde = $request->input('fecha_desde') ?: Carbon::yesterday()->toDateString();
        $fechaHasta = $request->input('fecha_hasta') ?: Carbon::today()->toDateString();
        $desde = Carbon::parse($fechaDesde)->startOfDay();
        $hasta = Carbon::parse($fechaHasta)->endOfDay();

        // Construir query base
        $query = Vodafone::query()->with(['asignado_a', 'user']);

        // Filtros según permisos
        if ($user->can('vodafone.recibe-asignacion') && !$user->can('vodafone.ver-global')) {
            $hoy = Carbon::today()->startOfDay();
            $manana = Carbon::today()->endOfDay();
            $query->where('asignado_a_id', $user->id)
                ->where('trazabilidad', 'asignado')
                ->whereBetween('created_at', [$hoy, $manana]);
        } else if ($user->can('vodafone.ver') && !$user->can('vodafone.ver-global') && !$user->can('vodafone.recibe-asignacion')) {
            // Solo ver los completados si solo tiene el permiso 'vodafone.ver'
            $query->where('trazabilidad', 'completado')
                ->whereBetween('created_at', [$desde, $hasta]);
        } else {
            $query->whereBetween('created_at', [$desde, $hasta]);
            if (!$user->can('vodafone.ver-global')) {
                $query->where('user_id', $user->id);
            }
        }

        // Obtener items y formatear fechas
        $items = $query->orderBy('id')->get();
        $hoy = Carbon::today();
        $ayer = Carbon::yesterday();
        $items->transform(function ($item) use ($hoy, $ayer) {
            // Formato de fecha amigable
            $fecha = Carbon::parse($item->created_at);
            $dia = $fecha->isSameDay($hoy) ? 'Hoy' : ($fecha->isSameDay($ayer) ? 'Ayer' : $fecha->format('d/m/Y'));
            $hora = $fecha->format('g:i A');
            $item->created_at_formatted = "$dia $hora";

            // Historial de asignaciones (cabeceras)
            $item->asignaciones_historial = VodafoneAsignacion::with(['asignadoDe:id,name', 'asignadoA:id,name', 'usuarioCambio:id,name'])
                ->where('vodafone_id', $item->id)
                ->orderByDesc('fecha')
                ->get();

            // Agregar historial de auditoría a cada cabecera y filtrar cambios irrelevantes
            foreach ($item->asignaciones_historial as $cabecera) {
                $cabecera->auditoria_historial = \App\Models\VodafoneAuditoria::with('usuario')
                    ->where('asignacion_id', $cabecera->id)
                    ->orderByDesc('fecha')
                    ->get()
                    ->map(function ($auditoria) {
                        $campos = $auditoria->campos_editados;
                        if (is_array($campos)) {
                            unset($campos['asignado_a_id']);
                            $auditoria->setAttribute('campos_editados', $campos);
                        }
                        $auditoria->hasRelevantChanges = is_array($auditoria->campos_editados) && count($auditoria->campos_editados) > 0;
                        return $auditoria;
                    })
                    ->filter(function ($auditoria) {
                        return $auditoria->hasRelevantChanges;
                    })
                    ->values();
            }

            // Filtrar cabeceras sin detalles relevantes
            $item->asignaciones_historial = $item->asignaciones_historial->filter(function ($cabecera) {
                return $cabecera->auditoria_historial && $cabecera->auditoria_historial->count() > 0;
            })->values();

            // Última cabecera relevante
            $ultimaAsignacion = $item->asignaciones_historial->first();
            $item->ultima_asignacion = $ultimaAsignacion;
            $item->auditoria_historial = $ultimaAsignacion ? $ultimaAsignacion->auditoria_historial : collect();

            return $item;
        });

        // Usuarios asignables
        $usuariosAsignables = User::permission('vodafone.recibe-asignacion')
            ->select('id', 'name')
            ->get();

        // Renderizar vista
        return Inertia::render('Vodafone', [
            'items' => $items,
            'success' => session('success'),
            'canViewGlobal' => $user->can('vodafone.ver-global'),
            'canAssign' => $user->can('vodafone.asignar'),
            'usuariosAsignables' => $usuariosAsignables,
            'fechaDesde' => $fechaDesde,
            'fechaHasta' => $fechaHasta,
        ]);
    }


    public function fetchPage(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $fechaDesde = $request->input('fecha_desde') ?: Carbon::yesterday()->toDateString();
        $fechaHasta = $request->input('fecha_hasta') ?: Carbon::today()->toDateString();

        $desde = Carbon::parse($fechaDesde)->startOfDay();
        $hasta = Carbon::parse($fechaHasta)->endOfDay();

        $query = Vodafone::query()->with(['asignado_a', 'user'])
            ->whereBetween('created_at', [$desde, $hasta]);

        // Filtro de trazabilidad si viene
        if ($request->filled('trazabilidad')) {
            $trazabilidad = $request->input('trazabilidad');
            if (is_array($trazabilidad)) {
                $query->whereIn('trazabilidad', $trazabilidad);
            } else {
                $query->where('trazabilidad', $trazabilidad);
            }
        }

        // Puedes agregar más filtros aquí si lo necesitas

        if (!$user->can('vodafone.ver-global')) {
            $query->where('user_id', $user->id);
        }

        $items = $query->orderBy('id')->get();

        // Formatear la fecha igual que en index
        $hoy = Carbon::today();
        $ayer = Carbon::yesterday();
        $items->transform(function ($item) use ($hoy, $ayer) {
            $fecha = Carbon::parse($item->created_at);
            if ($fecha->isSameDay($hoy)) {
                $dia = 'Hoy';
            } elseif ($fecha->isSameDay($ayer)) {
                $dia = 'Ayer';
            } else {
                $dia = $fecha->format('d/m/Y');
            }
            $hora = $fecha->format('g:i A');
            $item->created_at_formatted = "{$dia} {$hora}";
            return $item;
        });

        return response()->json([
            'items' => $items
        ]);
    }

    public function asignar(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:historial_registros_vodafone,id',
            'asignado_a_id' => 'required|exists:users,id',
        ]);

        $user = Auth::user();
        $ids = $request->input('ids');
        $asignadoA = $request->input('asignado_a_id');


        $cantidad = 0;
        foreach ($ids as $id) {
            $vodafone = Vodafone::find($id);
            if (!$vodafone) continue;
            $asignadoDe = $vodafone->asignado_a_id;
            // Solo crear asignación si realmente cambia el asignado
            if ($asignadoDe != $asignadoA) {
                $vodafone->update([
                    'asignado_a_id' => $asignadoA,
                    'trazabilidad' => 'asignado',
                    'updated_at' => now(),
                ]);
                $asignacion = \App\Models\VodafoneAsignacion::create([
                    'vodafone_id' => $vodafone->id,
                    'asignado_de_id' => $asignadoDe,
                    'asignado_a_id' => $asignadoA,
                    'user_id' => $user->id,
                    'motivo' => 'reasignacion',
                    'fecha' => now(),
                ]);
                // Log de auditoría básico
                \App\Models\VodafoneAuditoria::create([
                    'vodafone_id' => $vodafone->id,
                    'asignacion_id' => $asignacion->id,
                    'user_id' => $user->id,
                    'accion' => 'asignacion',
                    'campos_editados' => [
                        'asignado_a_id' => [
                            'old' => $asignadoDe,
                            'new' => $asignadoA,
                        ]
                    ],
                    'fecha' => now(),
                ]);
                $cantidad++;
            }
        }

        Log::info('Registros Vodafone asignados', [
            'ids' => $ids,
            'asignado_a_id' => $asignadoA,
            'asignador_id' => $user->id,
            'cantidad' => $cantidad,
        ]);

        return redirect()->back()->with('success', "{$cantidad} registro(s) asignado(s) correctamente.");
    }


    public function store(Request $request)
    {
        $data = $request->validate($this->validationRules());

        $user = Auth::user();
        $data['user_id'] = $user->id;

        Vodafone::create($data);

        return redirect()->route('vodafone.index')->with('success', 'Registro creado correctamente.');
    }

    public function update(Request $request, Vodafone $vodafone)
    {
        $data = $request->validate($this->validationRules($vodafone->id));
        $user = Auth::user();
        $data['user_id'] = $user->id;

        unset($data['asignado_a_id']);

        // Solo bloquear si el registro NO está en 'asignado' Y el usuario es el asignado actual
        if (!in_array($vodafone->trazabilidad, ['asignado']) && $vodafone->asignado_a_id === $user->id) {
            Log::warning('Intento de edición sobre registro no editable por usuario asignado', [
                'id' => $vodafone->id,
                'user_id' => $user->id,
                'trazabilidad_actual' => $vodafone->trazabilidad,
                'data_intentada' => $data,
            ]);
            // Devolver error para Inertia
            return redirect()->back()->withErrors([
                'general' => 'El registro ya no está disponible para edición. Actualiza la página.'
            ]);
        }

        // Detectar cambios campo por campo (sin asignado_a_id)
        $camposEditados = [];
        $cambios = [];
        $campos = [
            'marca_base',
            'origen_motivo_cancelacion',
            'nombre_cliente',
            'dni_cliente',
            'orden_trabajo_anterior',
            'telefono_principal',
            'telefono_adicional',
            'correo_referencia',
            'direccion_historico',
            'observaciones',
            'trazabilidad',
        ];
        foreach ($campos as $campo) {
            if (array_key_exists($campo, $data)) {
                $old = $vodafone->$campo;
                $new = $data[$campo];
                if ($old !== $new) {
                    $camposEditados[] = $campo;
                    $cambios[$campo] = [
                        'old' => $old,
                        'new' => $new,
                    ];
                }
            }
        }
        Log::info('Campos editados', [
            'id' => $vodafone->id,
            'campos' => $camposEditados,
            'cambios' => $cambios,
        ]);


        // Si todos los campos requeridos están completos, cambiar trazabilidad a 'completado'
        $camposRequeridos = [
            'marca_base',
            'origen_motivo_cancelacion',
            'nombre_cliente',
            'dni_cliente',
            'orden_trabajo_anterior',
            'telefono_principal',
            'telefono_adicional',
            'correo_referencia',
            'direccion_historico',
            'observaciones',
        ];
        $todosCompletos = true;
        foreach ($camposRequeridos as $campo) {
            if (empty($data[$campo])) {
                $todosCompletos = false;
                break;
            }
        }
        if ($todosCompletos) {
            $data['trazabilidad'] = 'completado';
        }

        $vodafone->update($data);

        // Guardar auditoría asociada a la última cabecera de asignación (si existe)
        if (count($camposEditados)) {
            $cambiosAuditoria = [];
            foreach ($camposEditados as $campo) {
                $cambiosAuditoria[$campo] = $cambios[$campo];
            }
            $ultimaAsignacion = \App\Models\VodafoneAsignacion::where('vodafone_id', $vodafone->id)
                ->orderByDesc('fecha')
                ->first();
            \App\Models\VodafoneAuditoria::create([
                'vodafone_id' => $vodafone->id,
                'asignacion_id' => $ultimaAsignacion ? $ultimaAsignacion->id : null,
                'user_id' => $user->id,
                'accion' => 'edicion',
                'campos_editados' => $cambiosAuditoria,
                'fecha' => now(),
            ]);
        }

        return redirect()->route('vodafone.index')->with('success', 'Registro actualizado correctamente.');
    }


    public function destroy(Request $request, Vodafone $vodafone)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($vodafone->user_id !== $user->id && !$user->can('vodafone.eliminar')) {
            abort(403, 'No autorizado');
        }

        $vodafone->delete();

        return redirect()->route('vodafone.index')->with('success', 'Registro eliminado correctamente.');
    }


    private function validationRules(?int $ignoreId = null): array
    {
        return [
            'trazabilidad' => ['nullable', 'in:pendiente,asignado,irrelevante,completado,agendado'],
            'marca_base' => 'nullable|string|max:255',
            'origen_motivo_cancelacion' => 'nullable|string|max:255',
            'nombre_cliente' => 'nullable|string|max:255',
            'dni_cliente' => ['required', 'string', 'max:255', 'unique:historial_registros_vodafone,dni_cliente,' . $ignoreId],
            'orden_trabajo_anterior' => 'nullable|string|max:255',
            'telefono_principal' => 'nullable|string|max:20',
            'telefono_adicional' => 'nullable|string|max:20',
            'correo_referencia' => 'nullable|email|max:255',
            'direccion_historico' => 'nullable|string|max:255',
            'observaciones' => 'nullable|string',
            'asignado_a_id' => 'nullable|exists:users,id',
        ];
    }
}
