<?php

namespace App\Http\Controllers;

use App\Models\Asistencia;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AsistenciaController extends Controller
{
    /**
     * Obtiene las asistencias del día actual
     */
    public function getAsistenciasDiarias()
    {
        try {
            $asistencias = Asistencia::with([
                'user' => function($query) {
                    $query->select('id', 'nombres_completos', 'documento_identidad', 'profile_photo', 'correo', 'jornada_id')
                        ->with(['programaFormacion', 'jornada']);
                }
            ])
            ->whereDate('fecha_hora', Carbon::today('America/Bogota'))
            ->orderBy('fecha_hora', 'desc')
            ->get();

            // Log para depuración
            Log::info('Asistencias obtenidas:', [
                'cantidad' => $asistencias->count(),
                'fecha' => Carbon::today('America/Bogota')->toDateString(),
                'primera_asistencia' => $asistencias->first()
            ]);

            return response()->json([
                'status' => 'success',
                'data' => $asistencias,
                'count' => $asistencias->count(),
                'timestamp' => now()->setTimezone('America/Bogota')->format('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener asistencias: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener las asistencias',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtiene las asistencias de un usuario específico
     */
    public function getAsistenciasByUsuario($id)
    {
        try {
            $asistencias = Asistencia::with([
                'user' => function($query) {
                    $query->with(['programaFormacion', 'jornada']);
                }
            ])
            ->where('user_id', $id)
            ->orderBy('fecha_hora', 'desc')
            ->get();

            return response()->json([
                'status' => 'success',
                'data' => $asistencias
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener asistencias por usuario: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener las asistencias del usuario',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Registra una nueva asistencia
     */
    public function registrarAsistencia(Request $request)
    {
        try {
            $request->validate([
                'user_id' => 'required|exists:users,id',
                'tipo' => 'required|in:entrada,salida,puntual,tardanza,ausencia'
            ]);

            $asistencia = Asistencia::create([
                'user_id' => $request->user_id,
                'tipo' => $request->tipo,
                'fecha_hora' => now()->setTimezone('America/Bogota'),
                'registrado_por' => auth()->id()
            ]);

            $asistencia->load([
                'user' => function($query) {
                    $query->with(['programaFormacion', 'jornada']);
                }
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Asistencia registrada correctamente',
                'data' => $asistencia
            ]);
        } catch (\Exception $e) {
            Log::error('Error al registrar asistencia: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Error al registrar la asistencia',
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 