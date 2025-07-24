<?php

namespace App\Http\Controllers;

use App\Models\Vodafone;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use App\Models\LogImportacionVodafone;
use App\Models\Vodafone as HistorialRegistroVodafone;


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

    public function importMasivo(Request $request)
    {
        $request->validate([
            'archivo' => 'required|file|mimes:xlsx,csv',
        ]);

        $user = Auth::user();
        $file = $request->file('archivo');
        $nombreOriginal = $file->getClientOriginalName();

        // Leer datos del archivo
        $data = []; // <- AQUI debes integrar Laravel Excel o PhpSpreadsheet

        // Crear log de importación
        $log = LogImportacionVodafone::create([
            'user_id' => $user->id,
            'nombre_archivo' => $nombreOriginal,
            'cantidad_registros' => count($data),
        ]);

        // Insertar registros
        foreach ($data as $fila) {
            HistorialRegistroVodafone::create([
                'user_id' => $user->id,
                'upload_id' => $log->id,
                'estado' => 'pendiente',
                'dni_nif_cif' => $fila['dni_nif_cif'] ?? null,
                'nombre_cliente' => $fila['nombre_cliente'] ?? null,
                'telefono_contacto' => $fila['telefono_contacto'] ?? null,
                // Agrega aquí los demás campos del Excel si deseas
            ]);
        }

        return redirect()->back()->with('success', 'Importación completada correctamente.');
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
