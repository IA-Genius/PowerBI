<?php

namespace App\Http\Controllers;

use App\Models\Reporte;
use App\Models\Cartera;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReportesController extends Controller
{
    // Listar carteras y reportes
    public function index()
    {
        $reportes = Reporte::with('cartera')->get();
        $carteras = Cartera::all();

        return Inertia::render('GestionarCarteras', [
            'reportes' => $reportes,
            'carteras' => $carteras,
            'success' => session('success'),
        ]);
    }

    // Crear un nuevo reporte
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'link' => 'required|string|max:255',
            'orden' => 'nullable|integer',
            'cartera_id' => 'required|exists:carteras,id',
        ]);

        $reporte = Reporte::create($request->only('nombre', 'link', 'orden', 'cartera_id'));

        return response()->json([
            'message' => 'Reporte creado correctamente.',
            'reporte' => $reporte->load('cartera'),
        ]);
    }

    // Actualizar un reporte existente
    public function update(Request $request, Reporte $reporte)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'link' => 'required|string|max:255',
            'orden' => 'nullable|integer',
            'cartera_id' => 'required|exists:carteras,id',
        ]);

        $reporte->update($request->only('nombre', 'link', 'orden', 'cartera_id'));

        return response()->json([
            'message' => 'Reporte actualizado correctamente.',
            'reporte' => $reporte->load('cartera'),
        ]);
    }

    // Eliminar un reporte
    public function destroy(Reporte $reporte)
    {
        $reporte->delete();

        return response()->json([
            'message' => 'Reporte eliminado correctamente.',
            'id' => $reporte->id,
        ]);
    }
}
