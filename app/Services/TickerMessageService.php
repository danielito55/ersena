<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TickerMessageService
{
    /**
     * Obtiene estadísticas básicas para usar en mensajes.
     * 
     * @return array
     */
    public function getStats(): array
    {
        $now = Carbon::now();
        $today = Carbon::today();

        // Determinar período del día
        $periodoDia = match(true) {
            $now->hour < 12 => 'mañana',
            $now->hour < 18 => 'tarde',
            default => 'noche'
        };

        return [
            'periodo_dia' => $periodoDia,
            'fecha' => [
                'dia' => $today->format('d'),
                'mes' => $today->format('m'),
                'año' => $today->format('Y'),
                'dia_semana' => $this->getDiaSemana($today->format('N')),
                'mes_nombre' => $this->getMesNombre($today->format('n'))
            ]
        ];
    }

    /**
     * Obtiene el nombre del día de la semana en español.
     */
    private function getDiaSemana(int $dia): string
    {
        return [
            1 => 'lunes',
            2 => 'martes',
            3 => 'miércoles',
            4 => 'jueves',
            5 => 'viernes',
            6 => 'sábado',
            7 => 'domingo'
        ][$dia] ?? '';
    }
    
    /**
     * Obtiene el nombre del mes en español.
     */
    private function getMesNombre(int $mes): string
    {
        return [
            1 => 'enero',
            2 => 'febrero',
            3 => 'marzo',
            4 => 'abril',
            5 => 'mayo',
            6 => 'junio',
            7 => 'julio',
            8 => 'agosto',
            9 => 'septiembre',
            10 => 'octubre',
            11 => 'noviembre',
            12 => 'diciembre'
        ][$mes] ?? '';
    }
    
    /**
     * Ejemplo de método para obtener número de asistencias del día.
     */
    public function getNumeroAsistencias(): int
    {
        $today = Carbon::today();
        
        return DB::table('asistencias')
            ->whereDate('fecha_hora', $today)
            ->where('tipo', 'entrada')
            ->count();
    }

    /**
     * Ejemplo de método para obtener todos los programa de formacion que hayan.
     */
    public function getProgramasDeFormacion(): array
    {
        return DB::table('programa_formacion')
            ->select('nombre_programa', 'numero_ficha')
            ->get()
            ->toArray();
    }

    public function getmarcapc(): string
    {
        return DB::table('devices')
        ->select('marca')
        ->distinct()
        ->pluck('marca');
    }

    

    //como obtener el ultimo aprendiz registrado en la plataforma
    public function getAprendizMasNuevo(): ?object
    {
        return DB::table('users as u')
            ->join('programa_formacion as p', 'u.id', '=', 'p.user_id')
            ->select('u.nombres_completos', 'p.nombre_programa', 'p.numero_ficha')
            ->orderBy('u.created_at', 'desc')
            ->first(); // solo uno
    }

    
    /**
     * Guía para extender este servicio:
     * 
     * 1. Agregar consultas de asistencia:
     *    public function getNumeroAsistencias(): int
     *    {
     *        return DB::table('asistencias')
     *            ->whereDate('fecha_hora', Carbon::today())
     *            ->where('tipo', 'entrada')
     *            ->count();
     *    }
     * 
     * 2. Consultar aprendices sin registro:
     *    public function getAprendicesSinRegistro(): array
     *    {
     *        return DB::table('users as u')
     *            ->join('programa_formacion as pf', 'u.id', '=', 'pf.user_id')
     *            ->select('u.nombres_completos', 'pf.nombre_programa', 'pf.numero_ficha')
     *            ->where('u.rol', 'aprendiz')
     *            ->whereNotIn('u.id', function($query) {
     *                $query->select('user_id')
     *                    ->from('asistencias')
     *                    ->whereDate('fecha_hora', Carbon::today());
     *            })
     *            ->limit(5)
     *            ->get()
     *            ->toArray();
     *    }
     * 
     * 3. Consultar entradas recientes:
     *    public function getUltimasEntradas(int $minutes = 2): array
     *    {
     *        $timeLimit = now()->subMinutes($minutes);
     *        
     *        return DB::table('asistencias')
     *            ->where('tipo', 'entrada')
     *            ->where('fecha_hora', '>=', $timeLimit)
     *            ->join('users', 'asistencias.user_id', '=', 'users.id')
     *            ->join('programa_formacion', 'programa_formacion.user_id', '=', 'users.id')
     *            ->select(...)
     *            ->get()
     *            ->toArray();
     *    }
     * 
     * 4. Obtener información de la ficha más puntual:
     *    public function getFichaMasPuntual(): ?array
     *    {
     *        // Implementar lógica para encontrar la ficha con mayor porcentaje de puntualidad
     *        // Retornar información como número de ficha, programa, porcentaje, etc.
     *    }
     * 
     * 5. Obtener información del último aprendiz registrado:
     *    public function getUltimoAprendizRegistrado(): ?array
     *    {
     *        // Consultar el último aprendiz registrado en el sistema
     *        // Verificar si el registro es reciente (últimos X minutos)
     *    }
     */
}