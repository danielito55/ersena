<?php
// Archivo: routes/api.php
// Rutas de la API para asistencias

use App\Http\Controllers\AsistenciaController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TickerMessageController;

Route::get('/asistencias/diarias', [AsistenciaController::class, 'getAsistenciasDiarias']);
Route::get('/asistencias/usuario/{id}', [AsistenciaController::class, 'getAsistenciasByUsuario']);
Route::get('/ticker-messages', [TickerMessageController::class, 'getMessages']);
Route::get('/ticker-events', [TickerMessageController::class, 'getRecentEvents']);
Route::get('/ticker-messages/refresh', [TickerMessageController::class, 'refresh']);
