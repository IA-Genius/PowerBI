<?php

namespace App\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;
use App\Models\User;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        Inertia::share([
            'auth' => fn() => Auth::check() ? [
                'id' => Auth::user()->id,
                'name' => Auth::user()->name,
                'email' => Auth::user()->email,
            ] : null,

            'userCarteras' => fn() => Auth::check()
                ? User::with('reportes.cartera')->find(Auth::id())?->reportes
                ->pluck('cartera')
                ->unique('id')
                ->values()
                : [],

            'userReportes' => fn() => Auth::check()
                ? User::with('reportes.cartera')->find(Auth::id())?->reportes
                : [],
        ]);
    }
}
