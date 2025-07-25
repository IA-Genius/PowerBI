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
use App\Http\Controllers\VodafoneController;

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


    Route::get('/newVodaFone', function () {
        return view('newVodaFone'); // Sin .vue ni extensión
    })->middleware('auth');




    // PERFIL Y DASHBOARD
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/reporte/{id}', [DashboardController::class, 'showReporte'])->name('dashboard.reporte');



    // ===================
    // ROLES
    // ===================+
    Route::middleware('can:roles.ver')->get('/roles', [RoleController::class, 'index'])->name('roles.index');
    Route::middleware('can:roles.crear')->post('/roles', [RoleController::class, 'store'])->name('roles.store');
    Route::middleware('can:roles.editar')->put('/roles/{role}', [RoleController::class, 'update'])->name('roles.update');
    Route::middleware('can:roles.eliminar')->delete('/roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');

    // ===================
    // USUARIOS
    // ===================
    Route::middleware('can:usuarios.ver')->get('/users', [UserController::class, 'index'])->name('users.index');
    Route::middleware('can:usuarios.crear')->post('/users', [UserController::class, 'store'])->name('users.store');
    Route::middleware('can:usuarios.editar')->put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::middleware('can:usuarios.eliminar')->delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

    // ===================
    // CARTERAS
    // ===================
    Route::middleware('can:carteras.ver')->get('/carteras', [CarterasController::class, 'index'])->name('carteras.index');
    Route::middleware('can:carteras.crear')->post('/carteras', [CarterasController::class, 'store'])->name('carteras.store');
    Route::middleware('can:carteras.editar')->put('/carteras/{cartera}', [CarterasController::class, 'update'])->name('carteras.update');
    Route::middleware('can:carteras.eliminar')->delete('/carteras/{cartera}', [CarterasController::class, 'destroy'])->name('carteras.destroy');

    // ===================
    // REPORTES
    // ===================
    Route::middleware('can:reportes.ver')->get('/reportes', [ReportesController::class, 'index'])->name('reportes.index');
    Route::middleware('can:reportes.crear')->post('/reportes', [ReportesController::class, 'store'])->name('reportes.store');
    Route::middleware('can:reportes.editar')->put('/reportes/{reporte}', [ReportesController::class, 'update'])->name('reportes.update');
    Route::middleware('can:reportes.eliminar')->delete('/reportes/{reporte}', [ReportesController::class, 'destroy'])->name('reportes.destroy');

    // ===================
    // MÓDULOS (desde pestaña Roles)
    // ===================
    Route::middleware('can:roles.crear')->post('/modulos', [ModuleController::class, 'store'])->name('modules.store');

    // ===================
    // VODAFONE
    // ===================
    Route::middleware('can:vodafone.ver')->get('/vodafone', [VodafoneController::class, 'index'])->name('vodafone.index');
    Route::middleware('can:vodafone.crear')->post('/vodafone', [VodafoneController::class, 'store'])->name('vodafone.store');
    Route::middleware('can:vodafone.editar')->put('/vodafone/{vodafone}', [VodafoneController::class, 'update'])->name('vodafone.update');
    Route::middleware('can:vodafone.eliminar')->delete('/vodafone/{vodafone}', [VodafoneController::class, 'destroy'])->name('vodafone.destroy');
    Route::middleware('can:vodafone.ver')->post('/vodafone/importar', [VodafoneController::class, 'import'])->name('vodafone.import');
    Route::post('/vodafone/asignar', [VodafoneController::class, 'asignar'])->name('vodafone.asignar');
    Route::get('/vodafone/page', [VodafoneController::class, 'fetchPage'])->name('vodafone.page');

});

// ===================
// RUTA BACKUP SI FALLA
// ===================
Route::fallback(function () {
    return redirect()->route(Auth::check() ? 'dashboard' : 'login');
});
