<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AprendizController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Ruta para el registro de aprendices
Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);

// Rutas protegidas por roles
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::post('/admin/verificar-asistencia', [AdminController::class, 'verificarAsistencia']);
    Route::post('/admin/buscar-por-qr', [AdminController::class, 'buscarPorQR']);
    Route::post('/admin/registrar-asistencia', [AdminController::class, 'registrarAsistencia'])->name('admin.registrar-asistencia');
    Route::get('/admin/exportar-excel', [AdminController::class, 'exportarExcel'])->name('admin.exportar-excel');
    Route::get('/admin/exportar-pdf', [AdminController::class, 'exportarPDF'])->name('admin.exportar.pdf');
});

Route::middleware(['auth', 'role:aprendiz'])->group(function () {
    Route::get('/aprendiz/dashboard', [AprendizController::class, 'dashboard'])->name('aprendiz.dashboard');
    Route::post('/aprendiz/actualizar-foto', [AprendizController::class, 'actualizarFoto'])->name('aprendiz.actualizar-foto');
    Route::post('/aprendiz/filtrar-registros', [AprendizController::class, 'filtrarRegistros'])->name('aprendiz.filtrar-registros');
});
