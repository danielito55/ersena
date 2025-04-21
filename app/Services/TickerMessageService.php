<?php

namespace App\Services;

use App\Models\Asistencia;
use App\Models\ProgramaFormacion;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TickerMessageService
{
    public function getStats(): array
    {
        $now = Carbon::now();
        $today = Carbon::today();

        // Obtener asistencias por ficha
        $asistenciasPorFicha = Asistencia::whereDate('fecha_hora', $today)
            ->where('tipo', 'entrada')
            ->join('users', 'asistencias.user_id', '=', 'users.id')
            ->join('programa_formacion', 'programa_formacion.user_id', '=', 'users.id')
            ->select('asistencias.*', 'programa_formacion.numero_ficha', 'programa_formacion.nombre_programa')
            ->get()
            ->groupBy('numero_ficha');

        // Encontrar la ficha con más asistencias
        $fichaConMasAsistencias = null;
        $maxAsistencias = 0;
        foreach ($asistenciasPorFicha as $ficha => $asistencias) {
            if ($asistencias->count() > $maxAsistencias) {
                $maxAsistencias = $asistencias->count();
                $fichaConMasAsistencias = [
                    'numero_ficha' => $ficha,
                    'cantidad' => $maxAsistencias,
                    'programa' => $asistencias->first()->nombre_programa
                ];
            }
        }

        // Calcular porcentaje de puntualidad por ficha
        $puntualidadPorFicha = [];
        foreach ($asistenciasPorFicha as $ficha => $asistencias) {
            $puntuales = $asistencias->filter(function ($asistencia) {
                return Carbon::parse($asistencia->fecha_hora)->format('H:i:s') <= 
                       $asistencia->user->jornada->hora_entrada;
            })->count();

            $puntualidadPorFicha[$ficha] = [
                'porcentaje' => $asistencias->count() > 0 ? 
                    round(($puntuales / $asistencias->count()) * 100) : 0,
                'programa' => $asistencias->first()->nombre_programa,
                'puntuales' => $puntuales,
                'total' => $asistencias->count()
            ];
        }

        // Encontrar la ficha más puntual
        $fichaMasPuntual = array_reduce(array_keys($puntualidadPorFicha), function ($carry, $ficha) use ($puntualidadPorFicha) {
            if (!$carry || $puntualidadPorFicha[$ficha]['porcentaje'] > $puntualidadPorFicha[$carry]['porcentaje']) {
                return $ficha;
            }
            return $carry;
        });

        // Determinar período del día
        $periodoDia = match(true) {
            $now->hour < 12 => 'mañana',
            $now->hour < 18 => 'tarde',
            default => 'noche'
        };

        // Obtener últimas llegadas
        $ultimasLlegadas = Asistencia::whereDate('fecha_hora', $today)
            ->where('tipo', 'entrada')
            ->join('users', 'asistencias.user_id', '=', 'users.id')
            ->join('programa_formacion', 'programa_formacion.user_id', '=', 'users.id')
            ->select('asistencias.*', 'programa_formacion.numero_ficha', 'programa_formacion.nombre_programa')
            ->orderBy('fecha_hora', 'desc')
            ->take(3)
            ->get();
            
        // Calcular estadísticas adicionales para mensajes más interesantes
        $totalAsistencias = $this->getNumeroAsistencias();
        $totalAprendices = DB::table('users')->where('rol', 'aprendiz')->count();
        $porcentajeAsistencia = $totalAprendices > 0 ? round(($totalAsistencias / $totalAprendices) * 100) : 0;
        
        // Calcular media de tiempo de llegada
        $tiemposLlegada = Asistencia::whereDate('fecha_hora', $today)
            ->where('tipo', 'entrada')
            ->join('users', 'asistencias.user_id', '=', 'users.id')
            ->join('jornadas', 'users.jornada_id', '=', 'jornadas.id')
            ->get()
            ->map(function ($asistencia) {
                $horaLlegada = Carbon::parse($asistencia->fecha_hora);
                $horaEntrada = Carbon::parse($asistencia->user->jornada->hora_entrada);
                $horaEntrada->setDate($horaLlegada->year, $horaLlegada->month, $horaLlegada->day);
                
                // Diferencia en minutos (negativa si llegó temprano, positiva si llegó tarde)
                return $horaLlegada->diffInMinutes($horaEntrada, false);
            });
            
        $mediaLlegada = $tiemposLlegada->count() > 0 ? $tiemposLlegada->avg() : 0;
        $mediaLlegadaFormatted = abs($mediaLlegada);
        $llegadaStatus = $mediaLlegada <= 0 ? 'antes' : 'después';
        
        // Información sobre la primera llegada del día
        $primeraLlegada = Asistencia::whereDate('fecha_hora', $today)
            ->where('tipo', 'entrada')
            ->join('users', 'asistencias.user_id', '=', 'users.id')
            ->join('programa_formacion', 'programa_formacion.user_id', '=', 'users.id')
            ->select('asistencias.*', 'users.nombres_completos', 'programa_formacion.numero_ficha', 'programa_formacion.nombre_programa')
            ->orderBy('fecha_hora', 'asc')
            ->first();
            
        $primeraLlegadaInfo = $primeraLlegada ? [
            'nombre' => $primeraLlegada->nombres_completos,
            'ficha' => $primeraLlegada->numero_ficha,
            'programa' => $primeraLlegada->nombre_programa,
            'hora' => Carbon::parse($primeraLlegada->fecha_hora)->format('H:i')
        ] : null;

        return [
            'ficha_mas_asistencias' => $fichaConMasAsistencias,
            'ficha_mas_puntual' => $fichaMasPuntual ? [
                'numero_ficha' => $fichaMasPuntual,
                'porcentaje' => $puntualidadPorFicha[$fichaMasPuntual]['porcentaje'],
                'programa' => $puntualidadPorFicha[$fichaMasPuntual]['programa'],
                'puntuales' => $puntualidadPorFicha[$fichaMasPuntual]['puntuales'],
                'total' => $puntualidadPorFicha[$fichaMasPuntual]['total']
            ] : null,
            'periodo_dia' => $periodoDia,
            'ultimas_llegadas' => $ultimasLlegadas->map(function ($asistencia) {
                return [
                    'ficha' => $asistencia->numero_ficha,
                    'programa' => $asistencia->nombre_programa,
                    'hora' => Carbon::parse($asistencia->fecha_hora)->format('H:i')
                ];
            })->toArray(),
            'total_asistencias' => $totalAsistencias,
            'total_aprendices' => $totalAprendices,
            'porcentaje_asistencia' => $porcentajeAsistencia,
            'media_llegada' => [
                'minutos' => round($mediaLlegadaFormatted),
                'status' => $llegadaStatus
            ],
            'primera_llegada' => $primeraLlegadaInfo,
            'fecha' => [
                'dia' => $today->format('d'),
                'mes' => $today->format('m'),
                'año' => $today->format('Y'),
                'dia_semana' => $this->getDiaSemana($today->format('N')),
                'mes_nombre' => $this->getMesNombre($today->format('n'))
            ]
        ];
    }

    public function getNumeroAsistencias(): int
    {
        $today = Carbon::today();
        
        return Asistencia::whereDate('fecha_hora', $today)
            ->where('tipo', 'entrada')
            ->count();
    }

    public function getAprendicesSinRegistro(): array
    {
        return DB::table('users as u')
            ->join('programa_formacion as pf', 'u.id', '=', 'pf.user_id')
            ->select('u.nombres_completos', 'pf.nombre_programa', 'pf.numero_ficha')
            ->where('u.rol', 'aprendiz')
            ->whereNotIn('u.id', function($query) {
                $query->select('user_id')
                    ->from('asistencias')
                    ->whereDate('fecha_hora', Carbon::today());
            })
            ->limit(5)
            ->get()
            ->toArray();
    }

    public function getUltimoAprendizRegistrado(): ?array
    {
        $ultimoAprendiz = DB::table('users as u')
            ->leftJoin('programa_formacion as pf', 'u.id', '=', 'pf.user_id')
            ->select(
                'u.id',
                'u.nombres_completos',
                'u.documento_identidad',
                'u.correo',
                'u.created_at',
                'pf.nombre_programa',
                'pf.numero_ficha'
            )
            ->where('u.rol', 'aprendiz')
            ->orderBy('u.created_at', 'DESC')
            ->first();

        if (!$ultimoAprendiz) {
            return null;
        }

        // Verificar si el registro es reciente (últimos 5 minutos)
        $tiempoRegistro = Carbon::parse($ultimoAprendiz->created_at);
        if ($tiempoRegistro->diffInMinutes(now()) > 5) {
            return null;
        }

        return (array) $ultimoAprendiz;
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
     * Obtiene las entradas registradas en los últimos minutos.
     * 
     * @param int $minutes Minutos hacia atrás para buscar
     * @return array Lista de entradas recientes
     */
    public function getUltimasEntradas(int $minutes = 2): array
    {
        $timeLimit = now()->subMinutes($minutes);
        
        $entradas = Asistencia::where('tipo', 'entrada')
            ->where('fecha_hora', '>=', $timeLimit)
            ->join('users', 'asistencias.user_id', '=', 'users.id')
            ->join('programa_formacion', 'programa_formacion.user_id', '=', 'users.id')
            ->select(
                'asistencias.*', 
                'users.nombres_completos', 
                'programa_formacion.numero_ficha', 
                'programa_formacion.nombre_programa'
            )
            ->orderBy('fecha_hora', 'desc')
            ->take(3)
            ->get();
            
        return $entradas->map(function ($entrada) {
            return [
                'id' => $entrada->id,
                'nombre' => $entrada->nombres_completos,
                'ficha' => $entrada->numero_ficha,
                'programa' => $entrada->nombre_programa,
                'hora' => Carbon::parse($entrada->fecha_hora)->format('H:i'),
                'timestamp' => $entrada->fecha_hora
            ];
        })->toArray();
    }
    
    /**
     * Obtiene las salidas registradas en los últimos minutos.
     * 
     * @param int $minutes Minutos hacia atrás para buscar
     * @return array Lista de salidas recientes
     */
    public function getUltimasSalidas(int $minutes = 2): array
    {
        $timeLimit = now()->subMinutes($minutes);
        
        $salidas = Asistencia::where('tipo', 'salida')
            ->where('fecha_hora', '>=', $timeLimit)
            ->join('users', 'asistencias.user_id', '=', 'users.id')
            ->join('programa_formacion', 'programa_formacion.user_id', '=', 'users.id')
            ->select(
                'asistencias.*', 
                'users.nombres_completos', 
                'programa_formacion.numero_ficha', 
                'programa_formacion.nombre_programa'
            )
            ->orderBy('fecha_hora', 'desc')
            ->take(3)
            ->get();
            
        return $salidas->map(function ($salida) {
            return [
                'id' => $salida->id,
                'nombre' => $salida->nombres_completos,
                'ficha' => $salida->numero_ficha,
                'programa' => $salida->nombre_programa,
                'hora' => Carbon::parse($salida->fecha_hora)->format('H:i'),
                'timestamp' => $salida->fecha_hora
            ];
        })->toArray();
    }
} 