<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Cartera;
use App\Models\Reporte;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class RoleController extends Controller
{
    public function index()
    {
        // 1) Traer datos necesarios
        $roles    = Role::with(['carteras', 'reportes'])->get();
        $carteras = Cartera::all();
        $reportes = Reporte::with('cartera')->get();

        // 2) Renderizar la vista Inertia con todo lo que el modal y la lista necesitan
        return Inertia::render('GestionarRoles', [
            'roles'     => $roles,
            'carteras'  => $carteras,
            'reportes'  => $reportes,
            'success'   => session('success'),
        ]);
    }

    public function store(Request $req)
    {
        $data = $req->validate([
            'name'        => 'required|string|max:255|unique:roles,name',
            'carteras'    => 'nullable|array',
            'carteras.*'  => 'exists:carteras,id',
            'reportes'    => 'nullable|array',
            'reportes.*'  => 'exists:reportes,id',
        ]);

        $role = Role::create(['name' => $data['name']]);
        $role->carteras()->sync($data['carteras'] ?? []);
        $role->reportes()->sync($data['reportes'] ?? []);

        return redirect()
            ->route('roles.index')
            ->with('success', "Rol «{$role->name}» creado correctamente.");
    }

    public function update(Request $req, Role $role)
    {
        $data = $req->validate([
            'name'        => ['required', 'string', 'max:255', Rule::unique('roles', 'name')->ignore($role->id)],
            'carteras'    => 'nullable|array',
            'carteras.*'  => 'exists:carteras,id',
            'reportes'    => 'nullable|array',
            'reportes.*'  => 'exists:reportes,id',
        ]);

        $role->update(['name' => $data['name']]);
        $role->carteras()->sync($data['carteras'] ?? []);
        $role->reportes()->sync($data['reportes'] ?? []);

        return redirect()
            ->route('roles.index')
            ->with('success', "Rol «{$role->name}» actualizado correctamente.");
    }

    public function destroy(Role $role)
    {
        $role->delete();

        return redirect()
            ->route('roles.index')
            ->with('success', "Rol «{$role->name}» eliminado correctamente.");
    }
}
