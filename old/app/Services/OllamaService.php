<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class OllamaService
{
    protected string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = 'http://192.168.230.3:9050/api/generate'; // Ollama API
    }

    public function generateResponse(string $model, string $prompt): array
    {
        $response = Http::post($this->baseUrl, [
            'model' => $model,
            'prompt' => $prompt,
            'stream' => false,
        ]);

        return $response->json();
    }

    public function generateResponseWithInstructions(string $model, array $instructions): array
    {
        try {
            // Format the instructions properly
            $formattedPrompt = implode("\n", array_map(fn($i) => "- " . $i, $instructions));

            // Send the request
            $response = Http::timeout(30)->post($this->baseUrl, [
                'model' => $model,
                'prompt' => "Follow these instructions:\n" . $formattedPrompt,
                'stream' => false, // Ensure full response
            ]);

            if ($response->failed()) {
                return [
                    'error' => 'Ollama request failed',
                    'status' => $response->status(),
                    'response' => $response->body(),
                ];
            }

            $data = $response->json();

            return ['response' => $data['response'] ?? 'No response from Ollama'];

        } catch (\Exception $e) {
            return ['error' => 'Failed to connect to Ollama', 'message' => $e->getMessage()];
        }
    }
}
