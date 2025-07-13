<?php

namespace App\Http\Controllers;

use App\Models\Cartera;
use App\Models\Reporte;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CarterasController extends Controller
{
    // Listar todas las carteras
    public function index()
    {
        $carteras = \App\Models\Cartera::all();
        $reportes = \App\Models\Reporte::with('cartera')->get();
        return Inertia::render('GestionarCarteras', [
            'carteras' => $carteras,
            'reportes' => $reportes,
            'success' => session('success'),
        ]);
    }

    // Guardar una nueva cartera
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'orden' => 'nullable|integer',
            'estado' => 'required|boolean',
        ]);

        Cartera::create($request->only('nombre', 'descripcion', 'orden', 'estado'));

        return redirect()->route('carteras.index')->with('success', 'Cartera creada correctamente.');
    }
    // Actualizar una cartera
    public function update(Request $request, Cartera $cartera)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'orden' => 'nullable|integer',
            'estado' => 'required|boolean',
        ]);

        $cartera->update($request->only('nombre', 'descripcion', 'orden', 'estado'));

        return redirect()->route('carteras.index')->with('success', 'Cartera actualizada correctamente.');
    }

    // Eliminar una cartera
    public function destroy(Cartera $cartera)
    {
        $cartera->delete();
        return redirect()->route('carteras.index')->with('success', 'Cartera eliminada correctamente.');
    }
}
