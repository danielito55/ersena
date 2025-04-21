<?php

namespace App\Http\Controllers;

use App\Services\GeminiService;
use App\Services\TickerMessageService;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class TickerMessageController extends Controller
{
    private TickerMessageService $tickerMessageService;
    private GeminiService $geminiService;
    
    // Mantener algunos mensajes de respaldo por si la API falla
    private array $mensajesRespaldo = [
        "¡La puntualidad es la clave del éxito! ⭐",
        "Tu compromiso construye un mejor futuro 🎯",
        "La disciplina de hoy es el éxito del mañana 💪",
        "Cada registro cuenta para tu formación 📚",
        "¡Gracias por tu compromiso con la excelencia! 🌟",
        "El aprendizaje es el camino al crecimiento 🌱",
        "Tu desarrollo profesional comienza con la puntualidad 🎓",
        "¡Juntos construimos un mejor SENA! 🏗️",
        "La excelencia es un hábito diario ✨",
        "Tu formación es nuestra prioridad 🎯"
    ];

    public function __construct(TickerMessageService $tickerMessageService, GeminiService $geminiService)
    {
        $this->tickerMessageService = $tickerMessageService;
        $this->geminiService = $geminiService;
    }

    public function getMessages(): JsonResponse
    {
        try {
            $stats = $this->tickerMessageService->getStats();
            $messages = [];
            
            Log::info('Generando mensajes del ticker', ['stats' => $stats]);

            // Obtener el ID de la vuelta (ciclo) del ticker
            $tickerCycle = request()->query('cycle', 0);
            
            // Usar una caché muy corta para evitar sobrecargar la API pero seguir ofreciendo variedad
            // La clave incluye el ciclo para que cada vuelta reciba mensajes diferentes
            $cacheKey = 'ticker_messages_cycle_' . $tickerCycle . '_' . date('Y-m-d_H_i');
            $cachedMessages = Cache::get($cacheKey);
            
            if ($cachedMessages) {
                Log::info('Usando mensajes en caché para el ciclo ' . $tickerCycle, ['count' => count($cachedMessages)]);
                return response()->json([
                    'status' => 'success',
                    'messages' => $cachedMessages,
                    'next_refresh' => 0, // Indicar al frontend que debe actualizar inmediatamente en la siguiente vuelta
                    'cycle' => $tickerCycle,
                    'source' => 'cache'
                ]);
            }
            
            // Control de rate limiting
            if (!$this->canMakeApiCalls()) {
                Log::warning('Rate limiting aplicado, usando mensajes por defecto');
                return $this->getDefaultMessages($tickerCycle);
            }

            // Seleccionar diferentes tipos de prompts según el ciclo
            // para tener variedad entre ciclos
            $prompts = $this->generarPromptsPorCiclo($stats, intval($tickerCycle));
            
            // Seleccionar aleatoriamente hasta 5 prompts diferentes para este ciclo
            shuffle($prompts);
            $selectedPrompts = array_slice($prompts, 0, 5);
            
            // Generar mensajes basados en los prompts seleccionados
            foreach ($selectedPrompts as $prompt) {
                if (!$this->canMakeApiCalls()) break;
                
                $mensaje = $this->geminiService->generateResponse($prompt);
                if ($mensaje) {
                    $messages[] = $mensaje;
                }
            }

            // Si no hay suficientes mensajes, agregar algunos de respaldo
            while (count($messages) < 3) {
                $messages[] = $this->mensajesRespaldo[array_rand($this->mensajesRespaldo)];
            }

            // Mezclar los mensajes y tomar máximo 5 para mostrar en cada ciclo
            shuffle($messages);
            $mensajesFiltrados = array_slice(array_filter($messages), 0, 5);
            
            // Guardar en caché por 30 minutos para reducir llamadas a la API
            Cache::put($cacheKey, $mensajesFiltrados, now()->addMinutes(30));
            
            Log::info('Mensajes del ticker generados exitosamente para el ciclo ' . $tickerCycle, [
                'total_mensajes' => count($mensajesFiltrados),
                'fuente' => 'gemini_api'
            ]);

            return response()->json([
                'status' => 'success',
                'messages' => $mensajesFiltrados,
                'next_refresh' => 0, // Indicar al frontend que debe actualizar inmediatamente en la siguiente vuelta
                'cycle' => $tickerCycle,
                'source' => 'api'
            ]);

        } catch (\Exception $e) {
            Log::error('Error generando mensajes del ticker', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Retornar mensajes por defecto en caso de error
            return $this->getDefaultMessages($tickerCycle ?? 0);
        }
    }
    
    /**
     * Determina si es seguro hacer más llamadas a la API basado en un límite simple.
     */
    private function canMakeApiCalls(): bool
    {
        $key = 'gemini_api_calls_' . date('Y-m-d_H');
        $count = Cache::get($key, 0);
        
        // Permitir hasta 50 llamadas por hora
        if ($count >= 50) {
            return false;
        }
        
        // Incrementar el contador
        Cache::put($key, $count + 1, now()->addHours(1));
        return true;
    }
    
    /**
     * Retorna mensajes por defecto en caso de error o límite de API.
     */
    private function getDefaultMessages(int $cycle = 0): JsonResponse
    {
        $mensajes = $this->mensajesRespaldo;
        shuffle($mensajes);
        
        // Agregar un mensaje de bienvenida personalizado al principio
        $hora = Carbon::now()->hour;
        $periodoDia = match(true) {
            $hora >= 5 && $hora < 12 => "mañana",
            $hora >= 12 && $hora < 18 => "tarde",
            default => "noche"
        };
        
        $bienvenidas = [
            'mañana' => ["¡Buenos días! Comienza un día lleno de aprendizaje 🌅", "¡Bienvenidos aprendices! Un nuevo día de oportunidades 🌞"],
            'tarde' => ["¡Buenas tardes! El aprendizaje continúa ☀️", "¡Bienvenidos! La tarde es perfecta para aprender 🌤️"],
            'noche' => ["¡Buenas noches! El conocimiento no descansa 🌙", "¡Bienvenidos! La noche es ideal para seguir aprendiendo ⭐"]
        ];
        
        array_unshift($mensajes, $bienvenidas[$periodoDia][array_rand($bienvenidas[$periodoDia])]);
        
        return response()->json([
            'status' => 'success',
            'messages' => array_slice($mensajes, 0, 5),
            'next_refresh' => 0, // Indicar al frontend que debe actualizar inmediatamente en la siguiente vuelta
            'cycle' => $cycle,
            'source' => 'default'
        ]);
    }
    
    /**
     * Selecciona diferentes tipos de prompts según el ciclo actual del ticker.
     * 
     * @param array $stats Datos estadísticos de asistencia
     * @param int $cycle Número de ciclo del ticker
     * @return array Lista de prompts para usar con la API de Gemini
     */
    private function generarPromptsPorCiclo(array $stats, int $cycle): array
    {
        $prompts = [];
        
        // Usamos el ciclo para alternar entre diferentes tipos de mensajes
        // y hacer que cada vuelta del ticker tenga un tema diferente
        switch ($cycle % 5) {
            case 0: // Ciclo 0: Mensajes de bienvenida y del día
                $prompts[] = "Genera un mensaje de bienvenida corto y motivador para aprendices del SENA en el período de la {$stats['periodo_dia']} en español. Debe ser positivo, breve (máximo 100 caracteres) y terminar con un emoji relevante.";
                $prompts[] = "Crea un mensaje corto y positivo para aprendices del SENA celebrando que hoy es {$stats['fecha']['dia_semana']} {$stats['fecha']['dia']} de {$stats['fecha']['mes_nombre']}. Máximo 100 caracteres, en español y con emoji relevante.";
                $prompts[] = "Genera un mensaje inspirador corto sobre comenzar el día con una actitud positiva en el SENA. Máximo 100 caracteres, en español y con emoji.";
                break;
                
            case 1: // Ciclo 1: Mensajes sobre estadísticas de asistencia
                $totalAsistencias = $stats['total_asistencias'];
                $prompts[] = "Crea un mensaje corto y positivo celebrando que hay $totalAsistencias aprendices registrados hoy en el SENA. Máximo 100 caracteres, en español y con emoji relevante.";
                
                if (isset($stats['porcentaje_asistencia'])) {
                    $porcentajeAsistencia = $stats['porcentaje_asistencia'];
                    $prompts[] = "Genera un mensaje motivador sobre el {$porcentajeAsistencia}% de asistencia en el SENA hoy. Máximo 100 caracteres, en español y con emoji.";
                }
                
                if (isset($stats['media_llegada'])) {
                    $mediaLlegada = $stats['media_llegada'];
                    $minutos = $mediaLlegada['minutos'];
                    $status = $mediaLlegada['status'];
                    $prompts[] = "Crea un mensaje sobre la puntualidad, mencionando que hoy los aprendices están llegando en promedio $minutos minutos $status de la hora establecida. Máximo 100 caracteres, en español y con emoji.";
                }
                break;
                
            case 2: // Ciclo 2: Mensajes sobre fichas destacadas
                if ($stats['ficha_mas_puntual']) {
                    $fichaPuntual = $stats['ficha_mas_puntual'];
                    $prompts[] = "Genera un mensaje corto felicitando a la ficha {$fichaPuntual['numero_ficha']} del programa {$fichaPuntual['programa']} por su puntualidad del {$fichaPuntual['porcentaje']}%. Máximo 100 caracteres, en español y con emoji.";
                }
                
                if ($stats['ficha_mas_asistencias']) {
                    $fichaAsistencias = $stats['ficha_mas_asistencias'];
                    $prompts[] = "Crea un mensaje celebrando que la ficha {$fichaAsistencias['numero_ficha']} del programa {$fichaAsistencias['programa']} tiene {$fichaAsistencias['cantidad']} aprendices registrados hoy. Máximo 100 caracteres, en español y con emoji.";
                }
                break;
                
            case 3: // Ciclo 3: Mensajes sobre aprendices específicos
                if (isset($stats['primera_llegada']) && $stats['primera_llegada']) {
                    $primera = $stats['primera_llegada'];
                    $prompts[] = "Genera un mensaje destacando a {$primera['nombre']} de la ficha {$primera['ficha']} por ser el primer aprendiz en llegar hoy a las {$primera['hora']}. Máximo 100 caracteres, en español y con emoji.";
                }
                
                if (!empty($stats['ultimas_llegadas'])) {
                    $llegada = $stats['ultimas_llegadas'][0];
                    $prompts[] = "Genera un mensaje corto dando la bienvenida a los aprendices de la ficha {$llegada['ficha']} del programa {$llegada['programa']} que llegaron a las {$llegada['hora']}. Máximo 100 caracteres, en español y con emoji.";
                }
                
                // Mensajes para aprendices sin registro
                $aprendicesSinRegistro = $this->tickerMessageService->getAprendicesSinRegistro();
                foreach (array_slice($aprendicesSinRegistro, 0, 2) as $aprendiz) {
                    $prompts[] = "Crea un recordatorio amable y corto para que {$aprendiz->nombres_completos} de la ficha {$aprendiz->numero_ficha} registre su asistencia en el SENA hoy. Máximo 100 caracteres, en español y con emoji.";
                }
                break;
                
            case 4: // Ciclo 4: Mensajes motivadores generales
                $temasMensajes = [
                    "Genera un mensaje motivador corto para aprendices del SENA sobre la importancia de la puntualidad. Máximo 100 caracteres, en español y con emoji inspirador.",
                    "Crea un mensaje motivador sobre la importancia de la disciplina en el aprendizaje técnico. Máximo 100 caracteres, en español y con emoji.",
                    "Escribe un mensaje motivador sobre el compromiso con la formación profesional en el SENA. Máximo 100 caracteres, en español y con emoji.",
                    "Redacta un mensaje inspirador sobre cómo la formación técnica transforma vidas. Máximo 100 caracteres, en español y con emoji.",
                    "Crea un mensaje positivo sobre el trabajo en equipo en el aprendizaje. Máximo 100 caracteres, en español y con emoji.",
                    "Genera una frase motivadora sobre la importancia de la constancia en el estudio. Máximo 100 caracteres, en español y con emoji.",
                    "Escribe un mensaje sobre la importancia de la práctica en la formación técnica. Máximo 100 caracteres, en español y con emoji.",
                    "Redacta un mensaje corto sobre cómo el aprendizaje continuo mejora las oportunidades laborales. Máximo 100 caracteres, en español y con emoji."
                ];
                
                // Elegir 5 mensajes aleatorios de la lista
                $prompts = array_merge($prompts, array_slice($temasMensajes, 0, 5));
                break;
        }
        
        // Si no hay suficientes prompts, agregar algunos generales
        if (count($prompts) < 3) {
            $prompts[] = "Genera un mensaje motivador corto para aprendices del SENA. Máximo 100 caracteres, en español y con emoji inspirador.";
            $prompts[] = "Crea una frase corta sobre la importancia de la educación técnica. Máximo 100 caracteres, en español y con emoji.";
            $prompts[] = "Escribe un mensaje positivo sobre el aprendizaje en el SENA. Máximo 100 caracteres, en español y con emoji.";
        }
        
        return $prompts;
    }

    /**
     * Obtiene los eventos recientes que deberían mostrarse en el ticker.
     * Estos eventos tienen prioridad sobre los mensajes normales.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRecentEvents(): JsonResponse
    {
        try {
            // Verificar eventos recientes (últimos 2 minutos)
            $events = [];
            
            // 1. Verificar si hay un nuevo aprendiz registrado
            $nuevoAprendiz = $this->tickerMessageService->getUltimoAprendizRegistrado();
            if ($nuevoAprendiz) {
                $promptNuevoAprendiz = "Genera un mensaje de bienvenida emocionante para {$nuevoAprendiz['nombres_completos']} que acaba de registrarse en la ficha {$nuevoAprendiz['numero_ficha']} del programa {$nuevoAprendiz['nombre_programa']}. Máximo 100 caracteres, en español y con emoji de celebración.";
                $mensajeAprendiz = $this->geminiService->generateResponse($promptNuevoAprendiz);
                
                if ($mensajeAprendiz) {
                    $events[] = [
                        'message' => $mensajeAprendiz,
                        'type' => 'new_user',
                        'priority' => 10,
                        'expires_at' => now()->addMinutes(5)->timestamp
                    ];
                }
            }
            
            // 2. Verificar últimas entradas (últimos 2 minutos)
            $ultimasEntradas = $this->tickerMessageService->getUltimasEntradas();
            foreach ($ultimasEntradas as $entrada) {
                $promptEntrada = "Genera un mensaje corto dando la bienvenida a {$entrada['nombre']} de la ficha {$entrada['ficha']} que acaba de llegar a las {$entrada['hora']}. Máximo 100 caracteres, en español y con emoji de saludo.";
                $mensajeEntrada = $this->geminiService->generateResponse($promptEntrada);
                
                if ($mensajeEntrada) {
                    $events[] = [
                        'message' => $mensajeEntrada,
                        'type' => 'entry',
                        'priority' => 8,
                        'expires_at' => now()->addMinutes(3)->timestamp
                    ];
                }
            }
            
            // 3. Verificar últimas salidas (últimos 2 minutos)
            $ultimasSalidas = $this->tickerMessageService->getUltimasSalidas();
            foreach ($ultimasSalidas as $salida) {
                $promptSalida = "Crea un mensaje corto de despedida para {$salida['nombre']} de la ficha {$salida['ficha']} que acaba de salir a las {$salida['hora']}. Máximo 100 caracteres, en español y con emoji de despedida.";
                $mensajeSalida = $this->geminiService->generateResponse($promptSalida);
                
                if ($mensajeSalida) {
                    $events[] = [
                        'message' => $mensajeSalida,
                        'type' => 'exit',
                        'priority' => 7,
                        'expires_at' => now()->addMinutes(3)->timestamp
                    ];
                }
            }
            
            // Si no hay eventos recientes, devolver una respuesta vacía
            if (empty($events)) {
                return response()->json([
                    'status' => 'success',
                    'events' => [],
                    'has_events' => false
                ]);
            }
            
            // Ordenar eventos por prioridad (mayor primero)
            usort($events, function($a, $b) {
                return $b['priority'] - $a['priority'];
            });
            
            return response()->json([
                'status' => 'success',
                'events' => $events,
                'has_events' => true
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error obteniendo eventos recientes', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'status' => 'error',
                'events' => [],
                'has_events' => false
            ]);
        }
    }
} 