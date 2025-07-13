<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UserController extends Controller
{
    // Listar todos los usuarios
    public function index(Request $request)
    {
        $users = User::all();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $users
            ]);
        }

        return Inertia::render('GestionarUsuarios', [
            'users' => $users,
            'success' => session('success'),
        ]);
    }

    // Guardar un nuevo usuario
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6',
            'active' => 'required|boolean',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
            'active' => $validated['active'],
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Usuario creado correctamente.',
                'data' => $user
            ], 201);
        }

        return redirect()->route('users.index')->with('success', 'Usuario creado correctamente.');
    }

    // Mostrar un usuario específico
    public function show(Request $request, User $user)
    {
        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $user
            ]);
        }

        return view('users.show', compact('user'));
    }

    // Actualizar un usuario
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'active' => 'required|boolean',
            'password' => 'nullable|string|min:6',
        ]);

        $data = $request->only('name', 'email', 'active');

        if (!empty($validated['password'])) {
            $data['password'] = bcrypt($validated['password']);
        }

        $user->update($data);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Usuario actualizado correctamente.',
                'data' => $user
            ]);
        }

        return redirect()->route('users.index')->with('success', 'Usuario actualizado correctamente.');
    }

    // Eliminar un usuario
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
}
