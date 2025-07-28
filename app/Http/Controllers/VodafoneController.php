<?php

namespace App\Http\Controllers;

use App\Models\Vodafone;
use App\Models\User;
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

        // Si no hay fechas, por defecto ayer y hoy
        $fechaDesde = $request->input('fecha_desde') ?: Carbon::yesterday()->toDateString();
        $fechaHasta = $request->input('fecha_hasta') ?: Carbon::today()->toDateString();

        $desde = Carbon::parse($fechaDesde)->startOfDay();
        $hasta = Carbon::parse($fechaHasta)->endOfDay();

        $query = Vodafone::query()
            ->with(['asignado_a', 'user']);

        // Si el usuario solo tiene el permiso de recibir asignación
        if ($user->can('vodafone.recibe-asignacion') && !$user->can('vodafone.ver-global')) {
            $hoy = Carbon::today()->startOfDay();
            $manana = Carbon::today()->endOfDay();
            $query->where('asignado_a_id', $user->id)
                ->where('trazabilidad', 'asignado')
                ->whereBetween('created_at', [$hoy, $manana]);
        } else {
            // Coordinador y otros roles
            $query->whereBetween('created_at', [$desde, $hasta]);
            if (!$user->can('vodafone.ver-global')) {
                $query->where('user_id', $user->id);
            }
        }

        $items = $query->orderBy('id')->get();

        // Formatear la fecha aquí
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

        $usuariosAsignables = User::permission('vodafone.recibe-asignacion')
            ->select('id', 'name')
            ->get();

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


        $cantidad = Vodafone::whereIn('id', $ids)->update([
            'asignado_a_id' => $asignadoA,
            'trazabilidad' => 'asignado',
            'updated_at' => now(),
        ]);

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

        // Detectar campos editados
        $camposEditados = [];
        foreach ($data as $campo => $valor) {
            if ($vodafone->$campo !== $valor) {
                $camposEditados[] = $campo;
            }
        }

        $vodafone->update($data);

        // Guardar auditoría solo si hubo cambios
        if (count($camposEditados)) {
            \App\Models\VodafoneAuditoria::create([
                'vodafone_id' => $vodafone->id,
                'user_id' => $user->id,
                'accion' => 'edicion',
                'campos_editados' => $camposEditados,
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
            'dni_cliente' => ['nullable', 'string', 'max:255', 'unique:historial_registros_vodafone,dni_cliente,' . $ignoreId],
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
