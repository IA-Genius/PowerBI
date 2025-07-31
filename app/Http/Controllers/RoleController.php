<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Cartera;
use App\Models\Reporte;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Log;

class RoleController extends Controller
{
    // =======================
    // MÉTODOS PRINCIPALES DE VISTA
    // =======================

    /**
     * Display the roles index with all roles, carteras, reportes y permisos.
     */
    public function index(): Response
    {
        // Obtener roles con relaciones
        $roles = $this->getRolesWithRelations();

        // Obtener datos auxiliares para formularios
        $formData = $this->getFormData();

        return Inertia::render('GestionarRoles', [
            'roles' => $roles,
            'carteras' => $formData['carteras'],
            'reportes' => $formData['reportes'],
            'permissions' => $formData['permissions'],
            'success' => session('success'),
        ]);
    }

    // =======================
    // MÉTODOS CRUD
    // =======================

    /**
     * Store a newly created role in storage.
     */
    public function store(Request $request)
    {
        Log::debug('📥 Datos recibidos en store', $request->all());

        try {
            $data = $request->validate($this->validationRules());
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('❌ Validación fallida', $e->errors());
            throw $e; // Re-lanza para que Inertia los reciba
        }

        // Crear rol básico
        $role = $this->createRole($data);

        // Sincronizar todas las relaciones
        $this->syncRoleRelations($role, $data);

        return redirect()
            ->route('roles.index')
            ->with('success', "Rol «{$role->name}» creado correctamente.");
    }

    /**
     * Update the specified role in storage.
     */
    public function update(Request $request, Role $role)
    {
        $data = $request->validate($this->validationRules($role->id));

        // Actualizar datos básicos del rol
        $this->updateRoleBasicData($role, $data);

        // Sincronizar todas las relaciones
        $this->syncRoleRelations($role, $data);

        return redirect()
            ->route('roles.index')
            ->with('success', "Rol «{$role->name}» actualizado correctamente.");
    }

    /**
     * Remove the specified role from storage.
     */
    public function destroy(Role $role)
    {
        $name = $role->name;
        $role->delete();

        return redirect()
            ->route('roles.index')
            ->with('success', "Rol «{$name}» eliminado correctamente.");
    }

    // =======================
    // MÉTODOS AUXILIARES PARA DATOS
    // =======================

    private function getRolesWithRelations()
    {
        return Role::with(['carteras', 'reportes', 'permissions'])->get();
    }

    private function getFormData()
    {
        return [
            'carteras' => Cartera::all(),
            'reportes' => Reporte::with('cartera')->get(),
            'permissions' => Permission::with('module')->get(),
        ];
    }

    // =======================
    // MÉTODOS AUXILIARES PARA CRUD
    // =======================

    private function createRole($data)
    {
        return Role::create([
            'name' => $data['name'],
            'guard_name' => 'web',
        ]);
    }

    private function updateRoleBasicData($role, $data)
    {
        $role->update([
            'name' => $data['name'],
            'guard_name' => 'web',
        ]);
    }

    // =======================
    // MÉTODOS AUXILIARES PARA RELACIONES
    // =======================

    private function syncRoleRelations($role, $data)
    {
        // Sincronizar relaciones pivot
        $role->carteras()->sync($data['carteras'] ?? []);
        $role->reportes()->sync($data['reportes'] ?? []);

        // Sincronizar permisos usando Spatie
        $role->syncPermissions($data['permissions'] ?? []);
    }

    // =======================
    // REGLAS DE VALIDACIÓN
    // =======================

    private function validationRules(?int $ignoreId = null): array
    {
        return [
            'name' => $ignoreId
                ? ['required', 'string', 'max:255', Rule::unique('roles', 'name')->ignore($ignoreId)]
                : 'required|string|max:255|unique:roles,name',
            'carteras' => 'nullable|array',
            'carteras.*' => 'exists:carteras,id',
            'reportes' => 'nullable|array',
            'reportes.*' => 'exists:reportes,id',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,name',
        ];
    }
}
