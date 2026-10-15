<?php

use App\Http\Controllers\App\CatalogoController;
use App\Http\Controllers\App\DashboardController;
use App\Http\Controllers\App\EmpresaController;
use App\Http\Controllers\App\EmpresaSwitchController;
use App\Http\Controllers\App\PacoteController;
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
    Route::get('/catalogo', [CatalogoController::class, 'index'])->name('catalogo.index');
    Route::post('/catalogo/categorias', [CatalogoController::class, 'categoriaStore'])->name('catalogo.categorias.store');
    Route::put('/catalogo/categorias/{categoria}', [CatalogoController::class, 'categoriaUpdate'])->name('catalogo.categorias.update');
    Route::delete('/catalogo/categorias/{categoria}', [CatalogoController::class, 'categoriaDestroy'])->name('catalogo.categorias.destroy');
    Route::post('/catalogo/itens', [CatalogoController::class, 'itemStore'])->name('catalogo.itens.store');
    Route::put('/catalogo/itens/{item}', [CatalogoController::class, 'itemUpdate'])->name('catalogo.itens.update');
    Route::delete('/catalogo/itens/{item}', [CatalogoController::class, 'itemDestroy'])->name('catalogo.itens.destroy');
    Route::post('/catalogo/adicionais', [CatalogoController::class, 'adicionalStore'])->name('catalogo.adicionais.store');
    Route::put('/catalogo/adicionais/{adicional}', [CatalogoController::class, 'adicionalUpdate'])->name('catalogo.adicionais.update');
    Route::delete('/catalogo/adicionais/{adicional}', [CatalogoController::class, 'adicionalDestroy'])->name('catalogo.adicionais.destroy');
    Route::get('/pacotes', [PacoteController::class, 'index'])->name('pacotes.index');
    Route::get('/pacotes/criar', [PacoteController::class, 'create'])->name('pacotes.create');
    Route::post('/pacotes', [PacoteController::class, 'store'])->name('pacotes.store');
    Route::get('/pacotes/{pacote}', [PacoteController::class, 'show'])->name('pacotes.show');
    Route::post('/pacotes/{pacote}/alternar', [PacoteController::class, 'toggle'])->name('pacotes.toggle');
});
