<?php

use App\Http\Controllers\App\DashboardController;
use App\Http\Controllers\App\EmpresaController;
use App\Http\Controllers\App\EmpresaSwitchController;
use App\Http\Controllers\AuthController;
use App\Http\Middleware\EnsureEmpresaContext;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check() ? redirect()->route('app.dashboard') : redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'createLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
    Route::get('/cadastro', [AuthController::class, 'createRegister'])->name('register');
    Route::post('/cadastro', [AuthController::class, 'register'])->name('register.store');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')->name('logout');

Route::prefix('app')->name('app.')->middleware(['auth', EnsureEmpresaContext::class])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/empresa', [EmpresaController::class, 'show'])->name('empresa.show');
    Route::put('/empresa', [EmpresaController::class, 'update'])->name('empresa.update');
    Route::post('/empresas/{empresa}/ativar', EmpresaSwitchController::class)->name('empresa.switch');
});
