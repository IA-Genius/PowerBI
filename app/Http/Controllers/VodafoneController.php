<?php

namespace App\Http\Controllers;

use App\Models\Vodafone;
use App\Models\User;
use App\Models\VodafoneAsignacion;
use App\Models\VodafoneAuditoria;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class VodafoneController extends Controller
{
    // =======================
    // MÉTODOS PRINCIPALES DE VISTA
    // =======================

    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Obtener parámetros de fecha
        $fechaDesde = $request->input('fecha_desde') ?: Carbon::yesterday()->toDateString();
        $fechaHasta = $request->input('fecha_hasta') ?: Carbon::today()->toDateString();

        // Validar y corregir fechas si están en orden incorrecto
        $fechaDesdeCarbon = Carbon::parse($fechaDesde);
        $fechaHastaCarbon = Carbon::parse($fechaHasta);

        if ($fechaDesdeCarbon->gt($fechaHastaCarbon)) {
            // Si fecha desde es mayor que fecha hasta, intercambiarlas
            $temp = $fechaDesde;
            $fechaDesde = $fechaHasta;
            $fechaHasta = $temp;
        }

        // Construir query según permisos del usuario
        $query = $this->buildQueryByPermissions($user, $fechaDesde, $fechaHasta);

        // Obtener datos paginados
        $paginatedData = $this->getPaginatedData($query, $request);

        // Obtener usuarios asignables
        $usuariosAsignables = $this->getAssignableUsers();

        // Renderizar vista
        return Inertia::render('Vodafone', [
            'paginatedData' => $paginatedData,
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

        // Validar y corregir fechas si están en orden incorrecto
        $fechaDesdeCarbon = Carbon::parse($fechaDesde);
        $fechaHastaCarbon = Carbon::parse($fechaHasta);

        if ($fechaDesdeCarbon->gt($fechaHastaCarbon)) {
            // Si fecha desde es mayor que fecha hasta, intercambiarlas
            $temp = $fechaDesde;
            $fechaDesde = $fechaHasta;
            $fechaHasta = $temp;
        }

        // Construir query con filtros de fecha y trazabilidad
        $query = $this->buildFetchQuery($user, $fechaDesde, $fechaHasta, $request);

        // Obtener datos paginados
        $paginatedData = $this->getPaginatedData($query, $request);

        return response()->json($paginatedData);
    }

    // =======================
    // MÉTODOS CRUD
    // =======================

    public function store(Request $request)
    {
        $data = $request->validate($this->validationRules());
        $data['user_id'] = Auth::id();

        Vodafone::create($data);

        return redirect()->route('vodafone.index')
            ->with('success', 'Registro creado correctamente.');
    }

    public function update(Request $request, Vodafone $vodafone)
    {
        $data = $request->validate($this->validationRules($vodafone->id));
        $user = Auth::user();
        $data['user_id'] = $user->id;

        // Remover asignado_a_id de los datos de actualización
        unset($data['asignado_a_id']);

        // Validar permisos de edición
        $validationResult = $this->validateEditPermissions($vodafone, $user, $data);
        if ($validationResult !== true) {
            return $validationResult; // Retorna el error
        }

        // Detectar cambios ANTES de auto-completar
        $cambios = $this->detectChanges($vodafone, $data);

        // Auto-completar si todos los campos están llenos
        $data = $this->autoCompleteIfReady($data);

        // Si autoCompleteIfReady cambió la trazabilidad, detectar este cambio también
        if (isset($data['trazabilidad']) && $data['trazabilidad'] !== $vodafone->trazabilidad) {
            if (!in_array('trazabilidad', $cambios['campos'])) {
                $cambios['campos'][] = 'trazabilidad';
                $cambios['cambios']['trazabilidad'] = [
                    'old' => $vodafone->trazabilidad,
                    'new' => $data['trazabilidad'],
                ];
            }
        }

        // Actualizar registro
        $vodafone->update($data);

        // Guardar auditoría si hay cambios
        if (!empty($cambios['campos'])) {
            $this->saveAuditTrail($vodafone, $user, $cambios['cambios']);
        }

        return redirect()->route('vodafone.index')
            ->with('success', 'Registro actualizado correctamente.');
    }

    public function destroy(Request $request, Vodafone $vodafone)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($vodafone->user_id !== $user->id && !$user->can('vodafone.eliminar')) {
            abort(403, 'No autorizado');
        }

        $vodafone->delete();

        return redirect()->route('vodafone.index')
            ->with('success', 'Registro eliminado correctamente.');
    }

    // =======================
    // MÉTODOS DE ASIGNACIÓN
    // =======================

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

        $cantidad = $this->processAssignments($ids, $asignadoA, $user);

        $this->logAssignmentActivity($ids, $asignadoA, $user->id, $cantidad);

        return redirect()->back()
            ->with('success', "{$cantidad} registro(s) asignado(s) correctamente.");
    }

    public function agendar(Request $request, Vodafone $vodafone)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Verificar permisos
        if (!$user->can('vodafone.agendar')) {
            abort(403, 'No tienes permisos para agendar registros');
        }

        // Verificar que el registro esté completado
        if ($vodafone->trazabilidad !== 'completado') {
            return redirect()->back()
                ->with('error', 'Solo se pueden agendar registros completados.');
        }

        // Cambiar la trazabilidad a 'agendado'
        $trazabilidadAnterior = $vodafone->trazabilidad;
        $vodafone->update([
            'trazabilidad' => 'agendado',
            'updated_at' => now(),
        ]);

        // Registrar en auditoría
        $this->saveScheduleAuditTrail($vodafone, $user, $trazabilidadAnterior);

        return redirect()->back()
            ->with('success', 'Registro agendado correctamente.');
    }

    // =======================
    // MÉTODOS AUXILIARES PARA QUERIES
    // =======================

    private function buildQueryByPermissions($user, $fechaDesde, $fechaHasta)
    {
        $desde = Carbon::parse($fechaDesde)->startOfDay();
        $hasta = Carbon::parse($fechaHasta)->endOfDay();

        $query = Vodafone::query()->with(['asignado_a', 'user']);

        if ($user->can('vodafone.filtrar')) {
            // Filtrador: puede seleccionar fechas, por defecto hoy y ayer
            $query->whereBetween('created_at', [$desde, $hasta]);
            if (!$user->can('vodafone.ver-global')) {
                $query->where('asignado_a_id', $user->id);
            }
        } elseif ($user->can('vodafone.recibe-asignacion') && !$user->can('vodafone.ver-global')) {
            $hoy = Carbon::today()->startOfDay();
            $manana = Carbon::today()->endOfDay();
            $query->where('asignado_a_id', $user->id)
                ->where('trazabilidad', 'asignado')
                ->whereBetween('created_at', [$hoy, $manana]);
        } elseif ($user->can('vodafone.ver') && !$user->can('vodafone.ver-global') && !$user->can('vodafone.recibe-asignacion')) {
            // Asesor Vodafone: Ver completados de TODOS los días (usando rango de fechas)
            $query->where('trazabilidad', 'completado')
                ->whereBetween('created_at', [$desde, $hasta]);
        } else {
            $query->whereBetween('created_at', [$desde, $hasta]);
            if (!$user->can('vodafone.ver-global')) {
                $query->where('user_id', $user->id);
            }
        }

        return $query;
    }

    private function buildFetchQuery($user, $fechaDesde, $fechaHasta, $request)
    {
        $desde = Carbon::parse($fechaDesde)->startOfDay();
        $hasta = Carbon::parse($fechaHasta)->endOfDay();

        $query = Vodafone::query()->with(['asignado_a', 'user']);

        // Filtro de búsqueda global
        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('nombre_cliente', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('dni_cliente', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('telefono_principal', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('telefono_adicional', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('correo_referencia', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('orden_trabajo_anterior', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('direccion_historico', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('observaciones', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('marca_base', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('origen_base', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('origen_motivo_cancelacion', 'LIKE', "%{$searchTerm}%");
            });
        }

        // Filtro de trazabilidad si viene
        if ($request->filled('trazabilidad')) {
            $trazabilidad = $request->input('trazabilidad');
            if (is_array($trazabilidad)) {
                $query->whereIn('trazabilidad', $trazabilidad);
            } else {
                $query->where('trazabilidad', $trazabilidad);
            }
        }

        // Filtros según permisos del usuario
        if ($user->can('vodafone.filtrar')) {
            $query->whereBetween('created_at', [$desde, $hasta]);
            if (!$user->can('vodafone.ver-global')) {
                $query->where('asignado_a_id', $user->id);
            }
        } elseif ($user->can('vodafone.recibe-asignacion') && !$user->can('vodafone.ver-global')) {
            // Usuario que recibe asignaciones - solo día actual
            $hoy = Carbon::today()->startOfDay();
            $finDelDia = Carbon::today()->endOfDay();
            $query->where('asignado_a_id', $user->id)
                ->where('trazabilidad', 'asignado')
                ->whereBetween('created_at', [$hoy, $finDelDia]);
        } elseif ($user->can('vodafone.ver') && !$user->can('vodafone.ver-global') && !$user->can('vodafone.recibe-asignacion')) {
            // Asesor Vodafone: Ver completados de TODOS los días (usando rango de fechas)
            $query->where('trazabilidad', 'completado')
                ->whereBetween('created_at', [$desde, $hasta]);
        } else {
            // Otros roles sí pueden usar rangos de fecha
            $query->whereBetween('created_at', [$desde, $hasta]);
            if (!$user->can('vodafone.ver-global')) {
                $query->where('user_id', $user->id);
            }
        }

        return $query;
    }

    private function getFormattedItems($query)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Contar total de registros antes de aplicar límites
        $totalCount = $query->count();

        // Aplicar limitación inteligente basada en el tamaño del dataset
        $limit = $this->calculateOptimalLimit($totalCount);

        if ($limit && $totalCount > $limit) {
            // Limitación aplicada
        }

        // Para asesor vodafone: ordenar de forma dispersa/desordenada
        if ($user->can('vodafone.ver') && !$user->can('vodafone.ver-global') && !$user->can('vodafone.recibe-asignacion')) {
            // Asesor Vodafone: ordenamiento disperso usando RAND()
            $queryWithOrder = $query->orderByRaw('RAND()');
        } else {
            // Otros roles: ordenamiento normal por ID
            $queryWithOrder = $query->orderBy('id');
        }

        // Aplicar límite si es necesario
        if ($limit && $totalCount > $limit) {
            $items = $queryWithOrder->limit($limit)->get();
        } else {
            $items = $queryWithOrder->get();
        }

        $hoy = Carbon::today();
        $ayer = Carbon::yesterday();

        return $items->transform(function ($item) use ($hoy, $ayer, $totalCount) {
            // Formato de fecha amigable
            $fecha = Carbon::parse($item->created_at);
            $dia = $fecha->isSameDay($hoy) ? 'Hoy' : ($fecha->isSameDay($ayer) ? 'Ayer' : $fecha->format('d/m/Y'));
            $hora = $fecha->format('g:i A');
            $item->created_at_formatted = "$dia $hora";

            // Obtener historial de asignaciones - optimizado para datasets grandes
            if ($totalCount > 5000) {
                // Para datasets muy grandes, no cargar historial completo
                $item->asignaciones_historial = collect();
                $item->ultima_asignacion = null;
                $item->auditoria_historial = collect();
            } else {
                // Para datasets normales, cargar historial completo
                $item->asignaciones_historial = $this->getAssignmentHistory($item->id);
                $ultimaAsignacion = $item->asignaciones_historial->first();
                $item->ultima_asignacion = $ultimaAsignacion;
                $item->auditoria_historial = $ultimaAsignacion ? $ultimaAsignacion->auditoria_historial : collect();
            }

            return $item;
        });
    }

    /**
     * Obtiene datos paginados para el frontend
     */
    private function getPaginatedData($query, $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Parámetros de paginación
        $page = max(1, (int) $request->input('page', 1));
        $perPage = max(10, min(100, (int) $request->input('per_page', 50))); // Entre 10 y 100
        $viewMode = $request->input('view_mode', 'grid'); // 'grid' o 'cards'

        // Contar total de registros
        $totalRecords = $query->count();

        // Calcular offset
        $offset = ($page - 1) * $perPage;

        // Aplicar ordenamiento según el rol del usuario
        if ($user->can('vodafone.ver') && !$user->can('vodafone.ver-global') && !$user->can('vodafone.recibe-asignacion')) {
            // Asesor Vodafone: ordenamiento disperso
            $queryWithOrder = $query->orderByRaw('RAND()');
        } else {
            // Otros roles: ordenamiento normal por ID ascendente (de menor a mayor)
            $queryWithOrder = $query->orderBy('id');
        }

        // Obtener registros paginados
        $items = $queryWithOrder
            ->offset($offset)
            ->limit($perPage)
            ->get();

        // Formatear datos
        $formattedItems = $this->formatItems($items, $totalRecords);

        // Calcular información de paginación
        $totalPages = (int) ceil($totalRecords / $perPage);
        $hasNextPage = $page < $totalPages;
        $hasPrevPage = $page > 1;

        return [
            'data' => $formattedItems,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total_records' => $totalRecords,
                'total_pages' => $totalPages,
                'has_next_page' => $hasNextPage,
                'has_prev_page' => $hasPrevPage,
                'from' => min($offset + 1, $totalRecords),
                'to' => min($offset + $perPage, $totalRecords),
            ],
            'view_mode' => $viewMode,
        ];
    }

    /**
     * Formatea los items para el frontend (versión optimizada)
     */
    private function formatItems($items, $totalCount = 0)
    {
        $hoy = Carbon::today();
        $ayer = Carbon::yesterday();

        return $items->transform(function ($item) use ($hoy, $ayer, $totalCount) {
            // Formato de fecha amigable
            $fecha = Carbon::parse($item->created_at);
            $dia = $fecha->isSameDay($hoy) ? 'Hoy' : ($fecha->isSameDay($ayer) ? 'Ayer' : $fecha->format('d/m/Y'));
            $hora = $fecha->format('g:i A');
            $item->created_at_formatted = "$dia $hora";

            // Para paginación, cargamos historial completo solo si es necesario
            // En el contexto de paginación, cargar historial básico
            $item->asignaciones_historial = $this->getAssignmentHistory($item->id);
            $ultimaAsignacion = $item->asignaciones_historial->first();
            $item->ultima_asignacion = $ultimaAsignacion;
            $item->auditoria_historial = $ultimaAsignacion ? $ultimaAsignacion->auditoria_historial : collect();

            return $item;
        });
    }

    /**
     * Calcula el límite óptimo basado en el tamaño total del dataset
     */
    private function calculateOptimalLimit($totalCount)
    {
        if ($totalCount <= 500) {
            return null; // Sin límite para datasets pequeños
        } elseif ($totalCount <= 1000) {
            return 500; // Datasets medianos: límite 700
        } elseif ($totalCount <= 7000) {
            return 500; // Datasets grandes: límite 700
        } elseif ($totalCount <= 15000) {
            return 500; // Datasets muy grandes: límite 500
        } else {
            return 500; // Datasets masivos: límite 5000
        }
    }

    private function getAssignableUsers()
    {
        return User::permission('vodafone.recibe-asignacion')
            ->select('id', 'name')
            ->get();
    }

    // =======================
    // MÉTODOS AUXILIARES PARA HISTORIAL Y AUDITORÍA
    // =======================

    private function getAssignmentHistory($vodafoneId)
    {
        $asignaciones = VodafoneAsignacion::with(['asignadoDe:id,name', 'asignadoA:id,name', 'usuarioCambio:id,name'])
            ->where('vodafone_id', $vodafoneId)
            ->orderByDesc('fecha')
            ->get();

        // Agregar historial de auditoría a cada cabecera
        foreach ($asignaciones as $cabecera) {
            $cabecera->auditoria_historial = $this->getAuditHistory($cabecera->id);
        }

        return $asignaciones;
    }

    private function getAuditHistory($asignacionId)
    {
        return VodafoneAuditoria::with('usuario')
            ->where('asignacion_id', $asignacionId)
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

    // =======================
    // MÉTODOS AUXILIARES PARA VALIDACIÓN Y ACTUALIZACIÓN
    // =======================

    private function validateEditPermissions($vodafone, $user, $data)
    {
        // Lógica especial para registros completados
        if ($vodafone->trazabilidad === 'completado') {
            // Si el usuario tiene permiso para editar completados, permitir edición completa
            if ($user->can('vodafone.editar-completados')) {
                // Permitir edición completa
                return true;
            } else {
                // Usuario sin permiso para editar completados
                return redirect()->back()->withErrors([
                    'general' => 'Este registro ya está completado y no puede ser editado.'
                ]);
            }
        }

        // Solo bloquear si el registro NO está en 'asignado' Y el usuario es el asignado actual
        if (!in_array($vodafone->trazabilidad, ['asignado']) && $vodafone->asignado_a_id === $user->id) {
            return redirect()->back()->withErrors([
                'general' => 'El registro ya no está disponible para edición. Actualiza la página.'
            ]);
        }

        return true;
    }

    private function detectChanges($vodafone, $data)
    {
        $camposEditados = [];
        $cambios = [];
        $campos = [
            'orden_trabajo_anterior',
            'origen_base',
            'marca_base',
            'origen_motivo_cancelacion',
            'nombre_cliente',
            'dni_cliente',
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

                // Normalizar valores para comparación (null vs vacío)
                $oldNormalized = $old === null ? '' : (string)$old;
                $newNormalized = $new === null ? '' : (string)$new;

                if ($oldNormalized !== $newNormalized) {
                    $camposEditados[] = $campo;
                    $cambios[$campo] = [
                        'old' => $old,
                        'new' => $new,
                    ];
                }
            }
        }

        return ['campos' => $camposEditados, 'cambios' => $cambios];
    }

    private function autoCompleteIfReady($data)
    {
        $camposRequeridos = [
            'orden_trabajo_anterior',
            'origen_base',
            'marca_base',
            'origen_motivo_cancelacion',
            'nombre_cliente',
            'dni_cliente',
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

        return $data;
    }

    private function saveAuditTrail($vodafone, $user, $cambios)
    {
        $ultimaAsignacion = VodafoneAsignacion::where('vodafone_id', $vodafone->id)
            ->orderByDesc('fecha')
            ->first();

        $auditoria = VodafoneAuditoria::create([
            'vodafone_id' => $vodafone->id,
            'asignacion_id' => $ultimaAsignacion ? $ultimaAsignacion->id : null,
            'user_id' => $user->id,
            'accion' => 'edicion',
            'campos_editados' => $cambios,
            'fecha' => now(),
        ]);

        return $auditoria;
    }

    private function saveScheduleAuditTrail($vodafone, $user, $trazabilidadAnterior)
    {
        $ultimaAsignacion = VodafoneAsignacion::where('vodafone_id', $vodafone->id)
            ->orderByDesc('fecha')
            ->first();

        VodafoneAuditoria::create([
            'vodafone_id' => $vodafone->id,
            'asignacion_id' => $ultimaAsignacion ? $ultimaAsignacion->id : null,
            'user_id' => $user->id,
            'accion' => 'agendado',
            'campos_editados' => [
                'trazabilidad' => [
                    'anterior' => $trazabilidadAnterior,
                    'nueva' => 'agendado'
                ]
            ],
            'fecha' => now(),
        ]);
    }

    // =======================
    // MÉTODOS AUXILIARES PARA ASIGNACIÓN
    // =======================

    private function processAssignments($ids, $asignadoA, $user)
    {
        $cantidad = 0;

        foreach ($ids as $id) {
            $vodafone = Vodafone::find($id);
            if (!$vodafone) continue;

            $asignadoDe = $vodafone->asignado_a_id;

            // Solo crear asignación si realmente cambia el asignado
            if ($asignadoDe != $asignadoA) {
                $this->executeAssignment($vodafone, $asignadoDe, $asignadoA, $user);
                $cantidad++;
            }
        }

        return $cantidad;
    }

    private function executeAssignment($vodafone, $asignadoDe, $asignadoA, $user)
    {
        // Actualizar registro
        $vodafone->update([
            'asignado_a_id' => $asignadoA,
            'trazabilidad' => 'asignado',
            'updated_at' => now(),
        ]);

        // Crear asignación
        $asignacion = VodafoneAsignacion::create([
            'vodafone_id' => $vodafone->id,
            'asignado_de_id' => $asignadoDe,
            'asignado_a_id' => $asignadoA,
            'user_id' => $user->id,
            'motivo' => 'reasignacion',
            'fecha' => now(),
        ]);

        // Log de auditoría
        VodafoneAuditoria::create([
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
    }

    private function logAssignmentActivity($ids, $asignadoA, $userId, $cantidad)
    {
        // Log activity without detailed logging
    }

    // =======================
    // REGLAS DE VALIDACIÓN
    // =======================

    private function validationRules(?int $ignoreId = null): array
    {
        return [
            'trazabilidad' => ['nullable', 'in:pendiente,asignado,irrelevante,completado,retornado'],
            'orden_trabajo_anterior' => 'nullable|string|max:255',
            'origen_base' => 'nullable|string|max:255',
            'nombre_cliente' => 'required|string|max:255',
            'dni_cliente' => ['required', 'string', 'max:255', 'unique:historial_registros_vodafone,dni_cliente,' . $ignoreId],
            'telefono_principal' => 'required|string|max:20',
            'telefono_adicional' => 'nullable|string|max:20',
            'correo_referencia' => 'nullable|email|max:255',
            'direccion_historico' => 'nullable|string|max:255',
            'marca_base' => 'nullable|string|max:255',
            'origen_motivo_cancelacion' => 'nullable|string|max:255',
            'observaciones' => 'nullable|string',
            'asignado_a_id' => 'nullable|exists:users,id',
        ];
    }

    // =======================
    // MÉTODOS DE PLANTILLA
    // =======================

    public function descargarPlantilla()
    {
        try {
            // Crear un nuevo archivo Excel
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Establecer el título de la hoja
            $sheet->setTitle('Plantilla Vodafone');

            // Cabeceras del archivo
            $headers = [
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

            // Escribir cabeceras en la primera fila
            foreach ($headers as $index => $header) {
                $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($index + 1);
                $sheet->setCellValue($columnLetter . '1', $header);
            }

            // Aplicar formato a las cabeceras
            $lastColumn = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($headers));
            $sheet->getStyle('A1:' . $lastColumn . '1')->applyFromArray([
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => '000000'],
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E3F2FD'],
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ],
                ],
            ]);

            // Datos de ejemplo SOLO para los campos solicitados
            $ejemplo = [
                'orden_trabajo_anterior' => 'OT123456',
                'origen_base' => 'XXXXXXXXXX',
                'nombre_cliente' => '',
                'dni_cliente' => '12345678X',
                'telefono_principal' => '600123456',
                'telefono_adicional' => '600654321',
                'correo_referencia' => 'example@xxxxx.com',
                'direccion_historico' => 'Av.Tacna 123, Lima-Perú',
                'marca_base' => '',
                'origen_motivo_cancelacion' => '',
                'observaciones' => ''
            ];


            // Escribir datos de ejemplo en la fila 2
            foreach ($headers as $colIndex => $header) {
                $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1);
                $sheet->setCellValue($columnLetter . '2', $ejemplo[$header] ?? '');
            }

            // Ajustar el ancho de las columnas automáticamente
            foreach (range(1, count($headers)) as $colIndex) {
                $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
                $sheet->getColumnDimension($columnLetter)->setAutoSize(true);
            }

            // Crear el writer para Excel
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

            // Crear el nombre del archivo
            $filename = 'plantilla_vodafone_' . date('Y-m-d') . '.xlsx';

            // Crear respuesta con el archivo Excel
            return response()->streamDownload(function () use ($writer) {
                $writer->save('php://output');
            }, $filename, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0',
            ]);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al generar la plantilla Excel: ' . $e->getMessage());
        }
    }
}
