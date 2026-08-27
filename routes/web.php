<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ContextController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\PagoController;
use App\Http\Controllers\SunatApiController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\ProfileController;

/*
|--------------------------------------------------------------------------
| Web Routes - MSA Facturación y Control Contable
|--------------------------------------------------------------------------
*/

// Rutas de Autenticación
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Cambio de Contexto Multi-Empresa & Multi-Sede
Route::post('/context/empresa', [ContextController::class, 'setEmpresa'])->name('context.set_empresa');
Route::post('/context/sede', [ContextController::class, 'setSede'])->name('context.set_sede');

// Rutas API para Consultas Asíncronas (SUNAT / RENIEC)
Route::get('/api/sunat/ruc/{ruc}', [SunatApiController::class, 'consultarRuc'])->name('api.sunat.ruc');
Route::get('/api/sunat/dni/{dni}', [SunatApiController::class, 'consultarDni'])->name('api.sunat.dni');

// Redirección Principal
Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Rutas del Sistema (Dashboard, Perfil, CxP, CxC, Pagos, Reportes)
Route::middleware(['web'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Módulo Perfil de Usuario & Cambio de Contraseña
    Route::prefix('perfil')->name('perfil.')->group(function () {
        Route::get('/', [ProfileController::class, 'show'])->name('show');
        Route::put('/', [ProfileController::class, 'update'])->name('update');
        Route::put('/password', [ProfileController::class, 'updatePassword'])->name('password');
    });

    // Módulo Proveedores (Cuentas por Pagar)
    Route::prefix('proveedores')->name('proveedores.')->group(function () {
        Route::get('/', [ProveedorController::class, 'index'])->name('index');
        Route::get('/crear', [ProveedorController::class, 'create'])->name('create');
        Route::post('/', [ProveedorController::class, 'store'])->name('store');
        Route::get('/{id}', [ProveedorController::class, 'show'])->name('show');
        Route::delete('/{id}', [ProveedorController::class, 'destroy'])->name('destroy');
    });

    // Módulo Clientes (Cuentas por Cobrar)
    Route::prefix('clientes')->name('clientes.')->group(function () {
        Route::get('/', [ClienteController::class, 'index'])->name('index');
        Route::get('/crear', [ClienteController::class, 'create'])->name('create');
        Route::post('/', [ClienteController::class, 'store'])->name('store');
        Route::get('/{id}', [ClienteController::class, 'show'])->name('show');
        Route::delete('/{id}', [ClienteController::class, 'destroy'])->name('destroy');
    });

    // Módulo Pagos / Adelantos
    Route::prefix('pagos')->name('pagos.')->group(function () {
        Route::post('/compra', [PagoController::class, 'storeCompra'])->name('store_compra');
        Route::post('/venta', [PagoController::class, 'storeVenta'])->name('store_venta');
        Route::delete('/{id}', [PagoController::class, 'destroy'])->name('destroy');
    });

    // Módulo Reportes
    Route::prefix('reportes')->name('reportes.')->group(function () {
        Route::get('/cuentas-por-pagar', [ReporteController::class, 'cuentasPorPagar'])->name('cxp');
        Route::get('/cuentas-por-cobrar', [ReporteController::class, 'cuentasPorCobrar'])->name('cxc');
    });
});

