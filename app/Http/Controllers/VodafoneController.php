<?php

namespace App\Http\Controllers;

use App\Models\Vodafone;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\VodafoneImport;

class VodafoneController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Cargar el usuario asignado correctamente
        $query = Vodafone::query()->with(['asignado_a']);

        if (!$user->can('vodafone.ver-global')) {
            $query->where('user_id', $user->id);
        }

        $items = $query->orderBy('id')->get();

        $usuariosAsignables = User::permission('vodafone.recibe-asignacion')
            ->select('id', 'name')
            ->get();

        return Inertia::render('Vodafone', [
            'items' => $items,
            'success' => session('success'),
            'canViewGlobal' => $user->can('vodafone.ver-global'),
            'canAssign' => $user->can('vodafone.asignar'),
            'usuariosAsignables' => $usuariosAsignables,
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
            'estado' => 'asignado',
            'updated_at' => now(),
        ]);


        Log::info('📌 Registros Vodafone asignados', [
            'ids' => $ids,
            'asignado_a_id' => $asignadoA,
            'asignador_id' => $user->id,
            'cantidad' => $cantidad,
        ]);

        return redirect()->back()->with('success', "{$cantidad} registro(s) asignado(s) correctamente.");
    }

    public function import(Request $request)
    {
        $request->validate([
            'archivo' => 'required|file|mimes:xlsx,csv',
        ]);

        Log::debug('\ud83d\udcc2 Archivo recibido', [
            'nombre' => $request->file('archivo')->getClientOriginalName(),
            'mime' => $request->file('archivo')->getMimeType(),
        ]);

        $user = $request->user();
        $file = $request->file('archivo');
        $nombreArchivo = $file->getClientOriginalName();

        Log::info('\ud83d\udce5 Importaci\u00f3n iniciada desde el formulario', [
            'user_id' => $user->id,
            'usuario' => $user->name,
            'archivo' => $nombreArchivo,
            'ip' => $request->ip(),
        ]);

        try {
            Excel::import(new VodafoneImport, $file);

            Log::info('\u2705 Importaci\u00f3n completada correctamente', [
                'archivo' => $nombreArchivo,
                'user_id' => $user->id,
            ]);

            return redirect()->back()->with('success', 'Importaci\u00f3n completada correctamente.');
        } catch (\Throwable $e) {
            Log::error('\u274c Error durante importaci\u00f3n', [
                'error' => $e->getMessage(),
                'archivo' => $nombreArchivo,
                'user_id' => $user->id,
            ]);

            return redirect()->back()->with('error', 'Ocurri\u00f3 un error al importar el archivo.');
        }
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
        $data = $request->validate($this->validationRules());
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $data['user_id'] = $user->id;

        $vodafone->update($data);

        return redirect()->route('vodafone.index')->with('success', 'Registro actualizado correctamente.');
    }

    public function destroy(Request $request, Vodafone $vodafone)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($vodafone->user_id !== $user->id && !$user->can('vodafone.destroy')) {
            abort(403, 'No autorizado');
        }

        $vodafone->delete();

        return redirect()->route('vodafone.index')->with('success', 'Registro eliminado correctamente.');
    }

    private function validationRules(): array
    {
        return [
            'dni_nif_cif' => 'nullable|string|max:255',
            'id_cliente' => 'nullable|string|max:255',
            'observacion_smart' => 'nullable|string',
            'oferta_comercial' => 'nullable|string',
            'operador_actual' => 'nullable|string|max:255',
            'telefono_contacto' => 'nullable|string|max:20',
            'nombre_cliente' => 'nullable|string|max:255',
            'direccion_instalacion' => 'nullable|string',
            'fecha_creacion' => 'nullable|date',
            'fecha_cierre' => 'nullable|date',
            'observaciones_back_office' => 'nullable|string',
            'tipificaciones' => 'nullable|string',
            'observaciones_operaciones' => 'nullable|string',
            'asignado_a_id' => 'nullable|exists:users,id',
        ];
    }
}
