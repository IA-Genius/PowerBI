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

        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $user ? $user->toArray() : null,
                'role' => $user ? $user->getRoleNames()->first() : null,
            ],

            'userCarteras' => fn() => $user
                ? $user->getEffectiveCarteras()->each(function ($cartera) {
                    $cartera->load('reportes');
                })
                : collect(),

            'userReportes' => fn() => $user
                ? $user->getEffectiveReportes()->each(function ($reporte) {
                    $reporte->load('cartera');
                })
                : collect(),

            'can' => $user
                ? $user->getAllPermissions()->pluck('name')->mapWithKeys(fn($name) => [$name => true])
                : [],
            'errors' => function () use ($request) {
                $errors = $request->session()->get('errors');
                if (!$errors) {
                    return new \stdClass();
                }

                $messages = $errors->getBag('default')->getMessages();
                $flattenedErrors = [];

                foreach ($messages as $field => $fieldErrors) {
                    $flattenedErrors[$field] = is_array($fieldErrors) ? $fieldErrors[0] : $fieldErrors;
                }

                return (object) $flattenedErrors;
            },

        ]);
    }
}
