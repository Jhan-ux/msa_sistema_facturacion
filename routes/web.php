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
use App\Http\Controllers\EmpresaController;
use App\Http\Controllers\SedeController;

/*
|--------------------------------------------------------------------------
| Web Routes - MSA Facturación y Control Contable
|--------------------------------------------------------------------------
*/

// Rutas de Autenticación Pública
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1')->name('login.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Redirección Principal
Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Rutas Protegidas del Sistema (Requieren Autenticación)
Route::middleware(['web', 'auth'])->group(function () {
    // Cambio de Contexto Multi-Empresa & Multi-Sede
    Route::post('/context/empresa', [ContextController::class, 'setEmpresa'])->name('context.set_empresa');
    Route::post('/context/sede', [ContextController::class, 'setSede'])->name('context.set_sede');

    // Rutas API para Consultas Asíncronas (SUNAT / RENIEC) con Throttling
    Route::middleware('throttle:60,1')->group(function () {
        Route::get('/api/sunat/ruc/{ruc}', [SunatApiController::class, 'consultarRuc'])->name('api.sunat.ruc');
        Route::get('/api/sunat/dni/{dni}', [SunatApiController::class, 'consultarDni'])->name('api.sunat.dni');
    });

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Módulo Perfil de Usuario & Cambio de Contraseña
    Route::prefix('perfil')->name('perfil.')->group(function () {
        Route::get('/', [ProfileController::class, 'show'])->name('show');
        Route::put('/', [ProfileController::class, 'update'])->name('update');
        Route::put('/password', [ProfileController::class, 'updatePassword'])->name('password');
    });

    // Módulo Empresas (Administración Multi-Empresa)
    Route::prefix('empresas')->name('empresas.')->group(function () {
        Route::get('/', [EmpresaController::class, 'index'])->name('index');
        Route::get('/crear', [EmpresaController::class, 'create'])->name('create');
        Route::post('/', [EmpresaController::class, 'store'])->name('store');
        Route::get('/{id}', [EmpresaController::class, 'show'])->name('show');
        Route::get('/{id}/editar', [EmpresaController::class, 'edit'])->name('edit');
        Route::put('/{id}', [EmpresaController::class, 'update'])->name('update');
        Route::patch('/{id}/toggle-status', [EmpresaController::class, 'toggleStatus'])->name('toggle_status');
        Route::delete('/{id}', [EmpresaController::class, 'destroy'])->name('destroy');
    });

    // Módulo Sedes (Administración Multi-Sede)
    Route::prefix('sedes')->name('sedes.')->group(function () {
        Route::get('/', [SedeController::class, 'index'])->name('index');
        Route::get('/crear', [SedeController::class, 'create'])->name('create');
        Route::post('/', [SedeController::class, 'store'])->name('store');
        Route::get('/{id}/editar', [SedeController::class, 'edit'])->name('edit');
        Route::put('/{id}', [SedeController::class, 'update'])->name('update');
        Route::patch('/{id}/toggle-status', [SedeController::class, 'toggleStatus'])->name('toggle_status');
        Route::delete('/{id}', [SedeController::class, 'destroy'])->name('destroy');
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

