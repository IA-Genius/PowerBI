<?php

namespace App\Http\Controllers;

use App\Models\Reporte;
use App\Models\Cartera;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReportesController extends Controller
{
    // =======================
    // MÉTODOS PRINCIPALES DE VISTA
    // =======================

    /**
     * Display the reports index with all reports and carteras.
     */
    public function index()
    {
        // Obtener reportes con relaciones
        $reportes = $this->getReportsWithRelations();

        // Obtener datos auxiliares para formularios
        $carteras = $this->getCarteras();

        return Inertia::render('GestionarReportes', [
            'reportes' => $reportes,
            'carteras' => $carteras,
            'success' => session('success'),
        ]);
    }

    // =======================
    // MÉTODOS CRUD
    // =======================

    /**
     * Store a newly created report in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate($this->validationRules());

        // Crear reporte
        $reporte = $this->createReport($data);

        return redirect()
            ->route('reportes.index')
            ->with('success', "Reporte «{$reporte->nombre}» creado correctamente.");
    }

    /**
     * Update the specified report in storage.
     */
    public function update(Request $request, Reporte $reporte)
    {
        $data = $request->validate($this->validationRules());

        // Actualizar reporte
        $this->updateReport($reporte, $data);

        return redirect()
            ->route('reportes.index')
            ->with('success', "Reporte «{$reporte->nombre}» actualizado correctamente.");
    }

    /**
     * Remove the specified report from storage.
     */
    public function destroy(Reporte $reporte)
    {
        $nombre = $reporte->nombre;
        $reporte->delete();

        return redirect()
            ->route('reportes.index')
            ->with('success', "Reporte «{$nombre}» eliminado correctamente.");
    }

    // =======================
    // MÉTODOS AUXILIARES PARA DATOS
    // =======================

    private function getReportsWithRelations()
    {
        return Reporte::with('cartera')->get();
    }

    private function getCarteras()
    {
        return Cartera::all();
    }

    // =======================
    // MÉTODOS AUXILIARES PARA CRUD
    // =======================

    private function createReport($data)
    {
        return Reporte::create([
            'nombre' => $data['nombre'],
            'link_desktop' => $data['link_desktop'],
            'link_mobile' => $data['link_mobile'],
            'icon' => $data['icon'],
            'orden' => $data['orden'],
            'cartera_id' => $data['cartera_id'],
        ]);
    }

    private function updateReport($reporte, $data)
    {
        $reporte->update([
            'nombre' => $data['nombre'],
            'link_desktop' => $data['link_desktop'],
            'link_mobile' => $data['link_mobile'],
            'icon' => $data['icon'],
            'orden' => $data['orden'],
            'cartera_id' => $data['cartera_id'],
        ]);
    }

    // =======================
    // REGLAS DE VALIDACIÓN
    // =======================

    private function validationRules(): array
    {
        return [
            'nombre' => 'required|string|max:255',
            'link_desktop' => 'required|string|max:255',
            'link_mobile' => 'nullable|string|max:255',
            'icon' => 'nullable|string',
            'orden' => 'nullable|integer',
            'cartera_id' => 'required|exists:carteras,id',
        ];
    }
}
