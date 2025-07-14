<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Reporte;
use App\Models\Cartera;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UserController extends Controller
{
    public function index()
    {
        // Cargar carteras asignadas a cada usuario para el multiselect
        $users = User::with(['reportes.cartera'])->get();
        $carteras = Cartera::with('reportes')->get();
        // Agregar carteras asignadas a cada usuario (solo objetos completos)
        foreach ($users as $user) {
            $user->carteras = $carteras->filter(function ($c) use ($user) {
                return $user->reportes->contains(function ($r) use ($c) {
                    return $r->cartera_id === $c->id;
                });
            })->values();
        }
        return Inertia::render('GestionarUsuarios', [
            'users' => $users,
            'reportes' => Reporte::with('cartera')->get(),
            'carteras' => $carteras,
            'success' => session('success'),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6',
            'active' => 'required|boolean',
            'reportes' => 'nullable|array',
            'reportes.*' => 'exists:reportes,id',
        ]);
        // Limpiar carteras si viene del frontend
        unset($validated['carteras']);

        $user = new User();
        $this->saveUser($user, $validated);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Usuario creado correctamente.',
                'data' => $user->load('reportes.cartera'),
            ], 201);
        }

        return redirect()->route('users.index')->with('success', 'Usuario creado correctamente.');
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:6',
            'active' => 'required|boolean',
            'reportes' => 'nullable|array',
            'reportes.*' => 'exists:reportes,id',
        ]);
        unset($validated['carteras']);

        $this->saveUser($user, $validated);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Usuario actualizado correctamente.',
                'data' => $user->load('reportes.cartera'),
            ]);
        }

        return redirect()->route('users.index')->with('success', 'Usuario actualizado correctamente.');
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
