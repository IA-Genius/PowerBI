<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        return [
            // todo lo que ya tienes de parent::share…
            'auth' => [
                'user' => $user ? $user->toArray() : null,
                'permissions' => $user
                    ? $user->getPermissionNames()->toArray()
                    : [],
            ],
            'userCarteras' => fn() => $user
                ? $user->carteras()->with('reportes')->get()
                : [],

            'userReportes' => fn() => $user
                ? $user->reportes()->with('cartera')->get()
                : [],

            'can' => [
                'manageUsers'    => $user ? $user->can('gestionar usuarios') : false,
                'manageRoles'    => $user ? $user->can('gestionar roles') : false,
                'manageCarteras' => $user ? $user->can('gestionar carteras') : false,
                'manageReportes' => $user ? $user->can('gestionar reportes') : false,
            ],

        ];
    }
}
