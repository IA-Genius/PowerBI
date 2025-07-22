<?php

namespace App\Http\Controllers;

use App\Models\Cartera;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CarterasController extends Controller
{
    public function index()
    {
        return Inertia::render('GestionarCarteras', [
            'carteras' => Cartera::all(),
            'reportes' => \App\Models\Reporte::with('cartera')->get(),
            'success' => session('success'),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'orden' => 'nullable|integer',
            'estado' => 'required|boolean',
        ]);

        $cartera = Cartera::create($validated);

        return redirect()
            ->route('carteras.index')
            ->with('success', "Cartera «{$cartera->nombre}» creada correctamente.");
    }

    public function update(Request $request, Cartera $cartera)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'orden' => 'nullable|integer',
            'estado' => 'required|boolean',
        ]);

        $cartera->update($validated);

        return redirect()
            ->route('carteras.index')
            ->with('success', "Cartera «{$cartera->nombre}» actualizada correctamente.");
    }

    public function destroy(Cartera $cartera)
    {
        $nombre = $cartera->nombre;
        $cartera->delete();

        return redirect()
            ->route('carteras.index')
            ->with('success', "Cartera «{$nombre}» eliminada correctamente.");
    }
}
