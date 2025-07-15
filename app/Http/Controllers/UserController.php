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
        // 1) Eager‐load a nivel de usuario y de rol:
        $users = User::with([
            'carteras',                 // las carteras personalizadas del usuario
            'reportes.cartera',         // los reportes personaliz. + su cartera
            'roles.carteras',           // las carteras que vienen de cada rol
            'roles.reportes.cartera',   // y los reportes que vienen de cada rol
        ])->get();

        $carteras = Cartera::with('reportes')->get();
        $reportes = Reporte::all();
        $roles    = Role::with(['carteras', 'reportes.cartera'])->get();

        return Inertia::render('GestionarUsuarios', compact('users', 'carteras', 'reportes', 'roles'));
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


        $user->syncRoles($data['roles'] ?? []);
        $user->carteras()->sync($data['carteras'] ?? []);
        $user->reportes()->sync($data['reportes'] ?? []);
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
        logger()->info('DATOS RECIBIDOS', $request->all());

        $user->update([
            'name'   => $data['name'],
            'email'  => $data['email'],
            'active' => $data['active'],
            // contraseña si llegó…
            ...($data['password'] ? ['password' => bcrypt($data['password'])] : []),
        ]);

        $user->syncRoles($data['roles'] ?? []);

        $user->carteras()->sync($data['carteras'] ?? []);
        $user->reportes()->sync($data['reportes'] ?? []);


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
