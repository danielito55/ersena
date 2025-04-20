<?php

namespace App\Services;

use App\Models\Asistencia;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PuntualidadService
{
    /**
     * Calcular puntuación de puntualidad basada en asistencias
     */
    public function calcularPuntuaciones($jornada_id = null, $fecha_inicio = null, $fecha_fin = null)
    {
        // Configurar fechas por defecto si no se proporcionan
        if (!$fecha_inicio) {
            $fecha_inicio = Carbon::now()->subDays(30)->startOfDay();
        }
        
        if (!$fecha_fin) {
            $fecha_fin = Carbon::now()->endOfDay();
        }
        
        // Construir consulta para filtrar por jornada si es necesario
        $query = User::select('users.id', 'users.nombres_completos', 'users.profile_photo', 'j.nombre as jornada', 'j.hora_entrada')
                     ->join('jornadas as j', 'users.jornada_id', '=', 'j.id')
                     ->where('users.rol', 'aprendiz');
        
        if ($jornada_id) {
            $query->where('users.jornada_id', $jornada_id);
        }
        
        $usuarios = $query->get();
        
        $resultados = [];
        
        foreach ($usuarios as $usuario) {
            // Obtener asistencias de entrada del usuario
            $asistencias = Asistencia::where('user_id', $usuario->id)
                              ->where('tipo', 'entrada')
                              ->whereBetween('fecha_hora', [$fecha_inicio, $fecha_fin])
                              ->get();
            
            if ($asistencias->count() == 0) {
                continue; // Saltar usuario sin asistencias en el período
            }
            
            $totalPuntos = 0;
            $totalAsistencias = $asistencias->count();
            $llegadasPuntuales = 0;
            $tiempoPromedio = 0;
            
            foreach ($asistencias as $asistencia) {
                $fechaAsistencia = Carbon::parse($asistencia->fecha_hora);
                
                // Determinar la hora de entrada esperada para ese día
                $horaEntradaEsperada = Carbon::parse($usuario->hora_entrada);
                $horaEntradaEsperada->setDateFrom($fechaAsistencia);
                
                // Calcular diferencia en minutos
                $diferencia = $fechaAsistencia->diffInMinutes($horaEntradaEsperada, false);
                $tiempoPromedio += $diferencia;
                
                // Asignar puntos según puntualidad
                if ($diferencia <= -5) { // Llegó 5+ minutos antes
                    $puntos = 10;
                    $llegadasPuntuales++;
                } elseif ($diferencia <= 0) { // Llegó a tiempo o antes
                    $puntos = 8;
                    $llegadasPuntuales++;
                } elseif ($diferencia <= 5) { // Llegó dentro de los 5 minutos de tolerancia
                    $puntos = 5;
                    $llegadasPuntuales++;
                } elseif ($diferencia <= 15) { // Llegó con retraso moderado
                    $puntos = 3;
                } else { // Llegó muy tarde
                    $puntos = 0;
                }
                
                $totalPuntos += $puntos;
            }
            
            // Calcular promedios y estadísticas
            $puntuacionPromedio = $totalAsistencias > 0 ? round($totalPuntos / $totalAsistencias, 1) : 0;
            $porcentajePuntualidad = $totalAsistencias > 0 ? round(($llegadasPuntuales / $totalAsistencias) * 100) : 0;
            $tiempoPromedioMinutos = $totalAsistencias > 0 ? round($tiempoPromedio / $totalAsistencias) : 0;
            
            // Obtener info del programa de formación
            $programa = DB::table('programa_formacion')
                          ->where('user_id', $usuario->id)
                          ->first();
            
            $resultados[] = [
                'id' => $usuario->id,
                'nombre' => $usuario->nombres_completos,
                'foto' => $usuario->profile_photo,
                'jornada' => $usuario->jornada,
                'programa' => $programa ? $programa->nombre_programa : 'N/A',
                'ficha' => $programa ? $programa->numero_ficha : 'N/A',
                'puntuacion' => $puntuacionPromedio,
                'total_puntos' => $totalPuntos,
                'asistencias' => $totalAsistencias,
                'puntualidad' => $porcentajePuntualidad,
                'tiempo_promedio' => $tiempoPromedioMinutos,
            ];
        }
        
        // Ordenar por puntuación total (descendente)
        usort($resultados, function($a, $b) {
            return $b['total_puntos'] - $a['total_puntos'];
        });
        
        // Limitar a top 10
        return array_slice($resultados, 0, 10);
    }
}