<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    protected $apiKey;
    protected $baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent';

    public function __construct()
    {
        $this->apiKey = config('services.gemini.key');
    }

    public function generateResponse($prompt)
    {
        try {
            if (empty($this->apiKey)) {
                Log::error('Gemini API key no está configurada');
                return null;
            }

            Log::info('Intentando generar respuesta con Gemini', [
                'prompt' => $prompt,
                'api_key_length' => strlen($this->apiKey)
            ]);

            $payload = [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => 0.7,
                    'topK' => 40,
                    'topP' => 0.95,
                    'maxOutputTokens' => 1024,
                ]
            ];

            Log::info('Enviando solicitud a Gemini', [
                'url' => $this->baseUrl,
                'payload' => $payload
            ]);

            $urlWithKey = $this->baseUrl . '?key=' . $this->apiKey;
            
            $response = Http::withHeaders([
                'Content-Type' => 'application/json'
            ])->post($urlWithKey, $payload);

            Log::info('Respuesta recibida de Gemini', [
                'status' => $response->status(),
                'headers' => $response->headers(),
                'body' => $response->json()
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['error'])) {
                    Log::error('Error en respuesta de Gemini', [
                        'error' => $data['error']
                    ]);
                    return null;
                }

                $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
                
                if ($text) {
                    Log::info('Respuesta generada exitosamente', [
                        'text' => $text
                    ]);
                    return $this->formatResponse($text);
                }
            }

            Log::error('Gemini API Error', [
                'status' => $response->status(),
                'response' => $response->json(),
                'url' => $this->baseUrl,
                'headers' => $response->headers()
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Gemini Service Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'prompt' => $prompt
            ]);

            return null;
        }
    }

    private function formatResponse($text)
    {
        // Asegurarse de que el texto termine con un emoji si no tiene uno
        if (!preg_match('/[\x{1F300}-\x{1F9FF}]/u', $text)) {
            $emojis = ['⭐', '🌟', '✨', '🎯', '🚀', '💪', '📚', '🎓', '🌱', '💫'];
            $text .= ' ' . $emojis[array_rand($emojis)];
        }
        
        // Limitar la longitud del texto a 150 caracteres máximo
        if (mb_strlen($text) > 150) {
            $text = mb_substr($text, 0, 147) . '...';
        }
        
        return $text;
    }
} 