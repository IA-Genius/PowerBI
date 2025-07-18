<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CarterasController;
use App\Http\Controllers\ReportesController;
use App\Http\Controllers\ModuleController;

// ===================
// RUTA INICIAL
// ===================
Route::get('/', function () {
    return redirect()->route(Auth::check() ? 'dashboard' : 'login');
});

// ===================
// AUTENTICACIÓN
// ===================
require __DIR__ . '/auth.php';

// ===================
// RUTAS PROTEGIDAS
// ===================
Route::middleware('auth')->group(function () {

    // PERFIL Y DASHBOARD
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/reporte/{id}', [DashboardController::class, 'showReporte'])->name('dashboard.reporte');

    // ===================
    // ROLES
    // ===================
    Route::middleware('can:roles.index')->group(function () {
        Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
    });

    Route::middleware('can:roles.create')->post('/roles', [RoleController::class, 'store'])->name('roles.store');
    Route::middleware('can:roles.edit')->put('/roles/{role}', [RoleController::class, 'update'])->name('roles.update');
    Route::middleware('can:roles.destroy')->delete('/roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');

    // ===================
    // USUARIOS
    // ===================
    Route::middleware('can:usuarios.index')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
    });

    Route::middleware('can:usuarios.create')->post('/users', [UserController::class, 'store'])->name('users.store');
    Route::middleware('can:usuarios.edit')->put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::middleware('can:usuarios.destroy')->delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

    // ===================
    // CARTERAS
    // ===================
    Route::middleware('can:carteras.index')->get('/carteras', [CarterasController::class, 'index'])->name('carteras.index');
    Route::middleware('can:carteras.create')->post('/carteras', [CarterasController::class, 'store'])->name('carteras.store');
    Route::middleware('can:carteras.edit')->put('/carteras/{cartera}', [CarterasController::class, 'update'])->name('carteras.update');
    Route::middleware('can:carteras.destroy')->delete('/carteras/{cartera}', [CarterasController::class, 'destroy'])->name('carteras.destroy');

    // ===================
    // REPORTES
    // ===================
    Route::middleware('can:reportes.index')->get('/reportes', [ReportesController::class, 'index'])->name('reportes.index');
    Route::middleware('can:reportes.create')->post('/reportes', [ReportesController::class, 'store'])->name('reportes.store');
    Route::middleware('can:reportes.edit')->put('/reportes/{reporte}', [ReportesController::class, 'update'])->name('reportes.update');
    Route::middleware('can:reportes.destroy')->delete('/reportes/{reporte}', [ReportesController::class, 'destroy'])->name('reportes.destroy');

    // ===================
    // MÓDULOS (desde pestaña Roles)
    // ===================
    Route::middleware('can:roles.create')->post('/modulos', [ModuleController::class, 'store'])->name('modules.store');

    // ===================
    // VODAFONE
    // ===================
    Route::middleware('can:vodafone.index')->get('/vodafone', [\App\Http\Controllers\VodafoneController::class, 'index'])->name('vodafone.index');
    Route::middleware('can:vodafone.create')->get('/vodafone/create', [\App\Http\Controllers\VodafoneController::class, 'create'])->name('vodafone.create');
    Route::middleware('can:vodafone.store')->post('/vodafone', [\App\Http\Controllers\VodafoneController::class, 'store'])->name('vodafone.store');
    Route::middleware('can:vodafone.edit')->get('/vodafone/{vodafone}/edit', [\App\Http\Controllers\VodafoneController::class, 'edit'])->name('vodafone.edit');
    Route::middleware('can:vodafone.update')->put('/vodafone/{vodafone}', [\App\Http\Controllers\VodafoneController::class, 'update'])->name('vodafone.update');
    Route::middleware('can:vodafone.destroy')->delete('/vodafone/{vodafone}', [\App\Http\Controllers\VodafoneController::class, 'destroy'])->name('vodafone.destroy');
});


// ===================
// RUTA BACKUP SI FALLA
// ===================
Route::fallback(function () {
    return redirect()->route(Auth::check() ? 'dashboard' : 'login');
});
