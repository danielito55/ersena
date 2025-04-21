<?php

namespace App\Http\Controllers;

use App\Services\GeminiService;
use App\Services\TickerMessageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class TickerMessageController extends Controller
{
    private GeminiService $geminiService;
    private TickerMessageService $tickerMessageService;
    
    // Mensajes de respaldo por si la API falla
    private array $mensajesRespaldo = [
        "¡La puntualidad es la clave del éxito! ⭐",
        "Tu compromiso construye un mejor futuro 🎯",
        "La disciplina de hoy es el éxito del mañana 💪"
    ];

    public function __construct(GeminiService $geminiService, TickerMessageService $tickerMessageService)
    {
        $this->geminiService = $geminiService;
        $this->tickerMessageService = $tickerMessageService;
    }
    public function getMessages(): JsonResponse
    {
        try {
            // Obtener estadísticas básicas
            $stats = $this->tickerMessageService->getStats();
            $totalAsistencias = $this->tickerMessageService->getNumeroAsistencias();
            $programasF = $this->tickerMessageService->getProgramasDeFormacion();
            $marca = $this->tickerMessageService->getmarcapc();
            
            // Inicializar el array de prompts
            $prompts = [];
            
            // Agregar prompts para cada programa de formación
            if (isset($programasF['programas']) && is_array($programasF['programas'])) {
                foreach ($programasF['programas'] as $programa) {
                    if (isset($programa->stdClass->nombre_programa) && isset($programa->stdClass->numero_ficha)) {
                        $prompts[] = "Genera un mensaje diciendo cosas buenas de {$programa->stdClass->nombre_programa} y de los aprendices que estan en la ficha {$programa->stdClass->numero_ficha} por su programa. Máximo 100 caracteres, en español y con emoji.";
                    }
                }
            }
            $prompts[] = "Genera un mensaje acerca de la marca {$marca}. Máximo 100 caracteres, en español y con emoji.";
            
            // Agregar prompts básicos para mensajes motivacionales
            $prompts[] = "Genera un mensaje motivador corto para estudiantes. Máximo 100 caracteres, en español y con emoji.";
            $prompts[] = "Crea una frase corta sobre la educación. Máximo 100 caracteres, en español y con emoji.";
            
            // Agregar prompts contextuales usando datos del servicio
            $prompts[] = "Genera un mensaje corto de buenos {$stats['periodo_dia']}s para estudiantes. Máximo 100 caracteres, en español y con emoji.";
            
            if ($totalAsistencias > 0) {
                $prompts[] = "Crea un mensaje celebrando que hay {$totalAsistencias} asistencias registradas hoy. Máximo 100 caracteres, en español y con emoji.";
            }
            
            // Agregar un mensaje sobre el día de la semana
            $prompts[] = "Escribe un mensaje positivo para este {$stats['fecha']['dia_semana']} {$stats['fecha']['dia']} de {$stats['fecha']['mes_nombre']}. Máximo 100 caracteres, en español y con emoji.";
            
            $messages = [];
            
            // Usar todos los prompts disponibles
            foreach ($prompts as $prompt) {
                $mensaje = $this->geminiService->generateResponse($prompt);
                if ($mensaje) {
                    $messages[] = $mensaje;
                }
            }

            // Si no hay suficientes mensajes, agregar algunos de respaldo
            if (empty($messages)) {
                $messages = $this->mensajesRespaldo;
            }
            
            return response()->json([
                'status' => 'success',
                'messages' => $messages
            ]);

        } catch (\Exception $e) {
            Log::error('Error generando mensajes del ticker', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'success',
                'messages' => $this->mensajesRespaldo
            ]);
        }
    }
}
