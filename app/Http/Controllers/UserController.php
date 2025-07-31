<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Reporte;
use App\Models\Cartera;
use App\Models\Role;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    // =======================
    // MÉTODOS PRINCIPALES DE VISTA
    // =======================

    public function index()
    {
        // Obtener usuarios con relaciones y datos efectivos
        $users = $this->getUsersWithEffectiveData();

        // Obtener datos auxiliares para el formulario
        $formData = $this->getFormData();

        return Inertia::render('GestionarUsuarios', [
            'users' => $users,
            'carteras' => $formData['carteras'],
            'reportes' => $formData['reportes'],
            'roles' => $formData['roles'],
            'success' => session('success'),
        ]);
    }

    // =======================
    // MÉTODOS CRUD
    // =======================

    public function store(Request $request)
    {
        $data = $request->validate($this->validationRules());

        // Crear usuario básico
        $user = $this->createUser($data);

        // Asignar roles
        $user->syncRoles($data['roles'] ?? []);

        // Aplicar lógica de herencia y asignaciones personalizadas
        $this->applyInheritanceLogic($user, $data);

        return redirect()
            ->route('users.index')
            ->with('success', "Usuario «{$user->name}» creado correctamente.");
    }

    public function update(Request $request, User $user)
    {
        Log::info('Datos recibidos en update()', $request->all());

        $data = $request->validate($this->validationRules($user->id));

        // Actualizar datos básicos del usuario
        $this->updateUserBasicData($user, $data);

        // Sincronizar roles
        $user->syncRoles($data['roles'] ?? []);

        // Aplicar lógica de herencia y asignaciones personalizadas
        $this->applyInheritanceLogic($user, $data);

        return redirect()
            ->route('users.index')
            ->with('success', "Usuario «{$user->name}» actualizado correctamente.");
    }

    public function destroy(Request $request, User $user)
    {
        $user->delete();

        return redirect()
            ->route('users.index')
            ->with('success', 'Usuario eliminado correctamente.');
    }

    // =======================
    // MÉTODOS AUXILIARES PARA DATOS
    // =======================

    private function getUsersWithEffectiveData()
    {
        return User::with([
            'carteras',
            'reportes.cartera',
            'roles.carteras',
            'roles.reportes.cartera',
        ])->get()->map(function ($user) {
            return [
                ...$user->toArray(),
                'effective_carteras' => $user->getEffectiveCarteras(),
                'effective_reportes' => $user->getEffectiveReportes(),
            ];
        });
    }

    private function getFormData()
    {
        return [
            'carteras' => Cartera::with('reportes')->get(),
            'reportes' => Reporte::all(),
            'roles' => Role::with(['carteras', 'reportes.cartera'])->get(),
        ];
    }

    // =======================
    // MÉTODOS AUXILIARES PARA CRUD
    // =======================

    private function createUser($data)
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
            'active' => $data['active'],
        ]);
    }

    private function updateUserBasicData($user, $data)
    {
        $updateData = [
            'name' => $data['name'],
            'email' => $data['email'],
            'active' => $data['active'],
        ];

        // Actualizar contraseña solo si se proporciona
        if (array_key_exists('password', $data) && $data['password']) {
            Log::info("Nueva contraseña para usuario {$user->id}: " . $data['password']);
            $updateData['password'] = bcrypt($data['password']);
        }

        $user->update($updateData);
    }

    // =======================
    // MÉTODOS AUXILIARES PARA HERENCIA DE PERMISOS
    // =======================

    private function applyInheritanceLogic($user, $data)
    {
        // Obtener IDs de carteras y reportes heredados de roles
        $inheritedData = $this->getInheritedDataFromRoles($user);

        // Filtrar asignaciones personalizadas (que no vienen del rol)
        $personalizedData = $this->getPersonalizedAssignments($data, $inheritedData);

        // Sincronizar solo las asignaciones personalizadas
        $user->carteras()->sync($personalizedData['carteras']);
        $user->reportes()->sync($personalizedData['reportes']);
    }

    private function getInheritedDataFromRoles($user)
    {
        return [
            'carteras' => $user->roles->flatMap->carteras->pluck('id')->unique(),
            'reportes' => $user->roles->flatMap->reportes->pluck('id')->unique(),
        ];
    }

    private function getPersonalizedAssignments($data, $inheritedData)
    {
        $carterasPersonalizadas = collect($data['carteras'] ?? [])
            ->filter(fn($id) => !$inheritedData['carteras']->contains($id));

        $reportesPersonalizados = collect($data['reportes'] ?? [])
            ->filter(fn($id) => !$inheritedData['reportes']->contains($id));

        return [
            'carteras' => $carterasPersonalizadas,
            'reportes' => $reportesPersonalizados,
        ];
    }

    // =======================
    // REGLAS DE VALIDACIÓN
    // =======================

    private function validationRules(?int $ignoreId = null): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => $ignoreId
                ? ['required', 'email', Rule::unique('users', 'email')->ignore($ignoreId)]
                : 'required|email|unique:users,email',
            'password' => $ignoreId ? 'nullable|string|min:6' : 'required|string|min:6',
            'active' => 'boolean',
            'carteras' => 'nullable|array',
            'carteras.*' => 'exists:carteras,id',
            'reportes' => 'nullable|array',
            'reportes.*' => 'exists:reportes,id',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id',
        ];
    }
}
