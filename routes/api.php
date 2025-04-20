<?php
// Archivo: routes/api.php
// Rutas de la API para asistencias

use App\Http\Controllers\AsistenciaController;
use Illuminate\Support\Facades\Route;

Route::get('/asistencias/diarias', [AsistenciaController::class, 'getAsistenciasDiarias']);
Route::get('/asistencias/usuario/{id}', [AsistenciaController::class, 'getAsistenciasByUsuario']);
