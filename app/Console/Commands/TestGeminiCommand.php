<?php

namespace App\Console\Commands;

use App\Services\GeminiService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class TestGeminiCommand extends Command
{
    protected $signature = 'gemini:test {prompt? : The prompt to send to Gemini}';
    protected $description = 'Test the Gemini API integration';

    protected $geminiService;

    public function __construct(GeminiService $geminiService)
    {
        parent::__construct();
        $this->geminiService = $geminiService;
    }

    public function handle()
    {
        $this->info('Testing Gemini API integration...');
        
        // Verificar la API key
        $apiKey = config('services.gemini.key');
        if (empty($apiKey)) {
            $this->error('GEMINI_API_KEY no está configurada en .env o está vacía.');
            return 1;
        }
        
        $this->info('API Key configurada: ' . substr($apiKey, 0, 5) . '...' . substr($apiKey, -5));
        
        // Obtener o usar el prompt de prueba
        $prompt = $this->argument('prompt') ?? 'Genera un mensaje motivador corto para aprendices del SENA. En español y con emoji.';
        
        $this->info('Enviando prompt a Gemini: ' . $prompt);
        
        // Intentar generar una respuesta
        try {
            $response = $this->geminiService->generateResponse($prompt);
            
            if ($response) {
                $this->info('Respuesta recibida correctamente:');
                $this->line('------------------------------------------');
                $this->line($response);
                $this->line('------------------------------------------');
                $this->info('¡Prueba exitosa!');
                return 0;
            } else {
                $this->error('No se recibió respuesta de Gemini. Verifica los logs para más detalles.');
                return 1;
            }
        } catch (\Exception $e) {
            $this->error('Error al comunicarse con Gemini: ' . $e->getMessage());
            Log::error('Error en test de Gemini', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }
} 