<?php

namespace App\Http\Controllers;

use App\Models\Cartera;
use App\Models\Reporte;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CarterasController extends Controller
{
    // =======================
    // MÉTODOS PRINCIPALES DE VISTA
    // =======================

    /**
     * Display the carteras index with all carteras and reportes.
     */
    public function index()
    {
        // Obtener carteras
        $carteras = $this->getCarteras();

        // Obtener reportes con relaciones
        $reportes = $this->getReportesWithRelations();

        return Inertia::render('GestionarCarteras', [
            'carteras' => $carteras,
            'reportes' => $reportes,
            'success' => session('success'),
        ]);
    }

    // =======================
    // MÉTODOS CRUD
    // =======================

    /**
     * Store a newly created cartera in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate($this->validationRules());

        // Crear cartera
        $cartera = $this->createCartera($data);

        return redirect()
            ->route('carteras.index')
            ->with('success', "Cartera «{$cartera->nombre}» creada correctamente.");
    }

    /**
     * Update the specified cartera in storage.
     */
    public function update(Request $request, Cartera $cartera)
    {
        $data = $request->validate($this->validationRules());

        // Actualizar cartera
        $this->updateCartera($cartera, $data);

        return redirect()
            ->route('carteras.index')
            ->with('success', "Cartera «{$cartera->nombre}» actualizada correctamente.");
    }

    /**
     * Remove the specified cartera from storage.
     */
    public function destroy(Cartera $cartera)
    {
        $nombre = $cartera->nombre;
        $cartera->delete();

        return redirect()
            ->route('carteras.index')
            ->with('success', "Cartera «{$nombre}» eliminada correctamente.");
    }

    // =======================
    // MÉTODOS AUXILIARES PARA DATOS
    // =======================

    private function getCarteras()
    {
        return Cartera::all();
    }

    private function getReportesWithRelations()
    {
        return Reporte::with('cartera')->get();
    }

    // =======================
    // MÉTODOS AUXILIARES PARA CRUD
    // =======================

    private function createCartera($data)
    {
        return Cartera::create($data);
    }

    private function updateCartera($cartera, $data)
    {
        $cartera->update($data);
    }

    // =======================
    // REGLAS DE VALIDACIÓN
    // =======================

    private function validationRules(): array
    {
        return [
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'orden' => 'nullable|integer',
            'estado' => 'required|boolean',
        ];
    }
}
