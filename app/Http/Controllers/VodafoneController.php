<?php

namespace App\Http\Controllers;

use App\Models\Vodafone;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class VodafoneController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $query = Vodafone::query();

        if (!$user->can('vodafone.view-global')) {
            $query->where('user_id', $user->id);
        }

        $items = $query->latest()->paginate(15);

        return Inertia::render('Vodafone/Index', [
            'items' => $items,
            'success' => session('success'),
        ]);
    }

    public function create()
    {
        return Inertia::render('Vodafone/Create');
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->validationRules());

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $data['user_id'] = $user->id;

        Vodafone::create($data);

        return redirect()->route('vodafone.index')->with('success', 'Registro creado correctamente.');
    }

    public function show(Vodafone $vodafone)
    {
        return Inertia::render('Vodafone/Show', [
            'vodafone' => $vodafone,
        ]);
    }

    public function edit(Vodafone $vodafone)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($vodafone->user_id !== $user->id && !$user->can('vodafone.view-global')) {
            abort(403, 'No autorizado');
        }

        return Inertia::render('Vodafone/Edit', [
            'vodafone' => $vodafone,
        ]);
    }

    public function update(Request $request, Vodafone $vodafone)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($vodafone->user_id !== $user->id && !$user->can('vodafone.view-global')) {
            abort(403, 'No autorizado');
        }

        $data = $request->validate($this->validationRules());

        $vodafone->update($data);

        return redirect()->route('vodafone.index')->with('success', 'Registro actualizado correctamente.');
    }

    public function destroy(Vodafone $vodafone)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($vodafone->user_id !== $user->id && !$user->can('vodafone.view-global')) {
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
        ];
    }
}
