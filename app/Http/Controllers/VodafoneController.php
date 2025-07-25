<?php

namespace App\Http\Controllers;

use App\Models\Vodafone;
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


        $query = Vodafone::query()->with(['user.roles']);


        if (!$user->can('vodafone.ver-global')) {
            $query->where('user_id', $user->id);
        }

        $items = $query->orderBy('id')->get();

        return Inertia::render('Vodafone', [
            'items' => $items,
            'success' => session('success'),
            'canViewGlobal' => $user->can('vodafone.ver-global'),
        ]);
    }
    public function import(Request $request)
    {
        $request->validate([
            'archivo' => 'required|file|mimes:xlsx,csv',
        ]);

        Log::debug('📂 Archivo recibido', [
            'nombre' => $request->file('archivo')->getClientOriginalName(),
            'mime' => $request->file('archivo')->getMimeType(),
        ]);
        $user = $request->user();
        $file = $request->file('archivo');
        $nombreArchivo = $file->getClientOriginalName();

        Log::info('📥 Importación iniciada desde el formulario', [
            'user_id' => $user->id,
            'usuario' => $user->name,
            'archivo' => $nombreArchivo,
            'ip' => $request->ip(),
        ]);

        try {
            Excel::import(new VodafoneImport, $file);

            Log::info('✅ Importación completada correctamente', [
                'archivo' => $nombreArchivo,
                'user_id' => $user->id,
            ]);

            return redirect()->back()->with('success', 'Importación completada correctamente.');
        } catch (\Throwable $e) {
            Log::error('❌ Error durante importación', [
                'error' => $e->getMessage(),
                'archivo' => $nombreArchivo,
                'user_id' => $user->id,
            ]);

            return redirect()->back()->with('error', 'Ocurrió un error al importar el archivo.');
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
            abort(403, 'No autorizado defefef');
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
        ];
    }
}
