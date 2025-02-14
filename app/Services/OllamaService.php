<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class OllamaService
{
    protected $ollamaUrl;

    public function __construct()
    {
        $this->ollamaUrl = 'http://localhost:11434/api/generate'; // Default Ollama API endpoint
    }

    public function generateResponse($chatHistory, $model = 'llama3.2:3b')
    {
        // $response = Http::post($this->ollamaUrl, [
        //     'model' => $model,
        //     'prompt' => $prompt,
        //     'stream' => false,
        // ]);

        // return $response->json();
        $formattedHistory = array_map(function ($message) {
            return $message['role'] . ": " . $message['content'];
        }, $chatHistory);

        $fullPrompt = implode("\n", $formattedHistory);

        $response = Http::timeout(300)->post($this->ollamaUrl, [
            'model' => $model,
            'prompt' => $fullPrompt,
            'stream' => false,
        ]);

        return $response->json();
    }
}
