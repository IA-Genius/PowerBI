<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Reporte;
use App\Models\Cartera;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Role;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {

        $users = User::with(['reportes.cartera'])->get();
        $carteras = Cartera::with('reportes')->get();

        foreach ($users as $user) {
            $user->carteras = $carteras->filter(function ($c) use ($user) {
                return $user->reportes->contains(function ($r) use ($c) {
                    return $r->cartera_id === $c->id;
                });
            })->values();
        }
        return Inertia::render('GestionarUsuarios', [
            'users'      => User::with(['carteras', 'reportes', 'roles'])->get(),
            'carteras'   => Cartera::with('reportes')->get(),
            'reportes'   => Reporte::all(),
            'roles'      => Role::all(),
            'success'    => session('success'),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => 'required|email|unique:users,email',
            'password'  => 'required|string|min:6',
            'active'    => 'boolean',
            'carteras'  => 'nullable|array',
            'carteras.*' => 'exists:carteras,id',
            'reportes'  => 'nullable|array',
            'reportes.*' => 'exists:reportes,id',
            'roles'     => 'nullable|array',
            'roles.*'   => 'exists:roles,id',
        ]);

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => bcrypt($data['password']),
            'active'   => $data['active'],
        ]);

        $user->carteras()->sync($data['carteras'] ?? []);
        $user->reportes()->sync($data['reportes'] ?? []);
        $user->syncRoles($data['roles'] ?? []);
        return redirect()
            ->route('users.index')
            ->with('success', "Usuario «{$user->name}» creado correctamente.");
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'password'  => 'nullable|string|min:6',
            'active'    => 'boolean',
            'carteras'  => 'nullable|array',
            'carteras.*' => 'exists:carteras,id',
            'reportes'  => 'nullable|array',
            'reportes.*' => 'exists:reportes,id',
            'roles'     => 'nullable|array',
            'roles.*'   => 'exists:roles,id',
        ]);

        $user->update([
            'name'   => $data['name'],
            'email'  => $data['email'],
            'active' => $data['active'],
            // Solo actualizamos contraseña si vino
            ...($data['password'] ? ['password' => bcrypt($data['password'])] : [])
        ]);

        $user->carteras()->sync($data['carteras'] ?? []);
        $user->reportes()->sync($data['reportes'] ?? []);
        $user->syncRoles($data['roles'] ?? []);    // ← sincronizamos roles

        return redirect()
            ->route('users.index')
            ->with('success', "Usuario «{$user->name}» actualizado correctamente.");
    }

    public function destroy(Request $request, User $user)
    {
        $user->delete();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Usuario eliminado correctamente.'
            ]);
        }

        return redirect()->route('users.index')->with('success', 'Usuario eliminado correctamente.');
    }

    private function saveUser(User $user, array $data): void
    {
        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->active = $data['active'];

        if (!empty($data['password'])) {
            $user->password = bcrypt($data['password']);
        }

        $user->save();

        $user->reportes()->sync($data['reportes'] ?? []);
    }
}
