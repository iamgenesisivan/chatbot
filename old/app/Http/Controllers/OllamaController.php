<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\OllamaService;

class OllamaController extends Controller
{
    protected $ollamaService;

    public function __construct(OllamaService $ollamaService)
    {
        $this->ollamaService = $ollamaService;
    }

    public function askOllama(Request $request)
    {
        // $data = $this->ollamaService->generateResponse(
        //     'llama3.2:3b', // Change model if needed
        //     $request->input('prompt', [
        //         "system" => ''
        //     ])
        // );

        $instructions = [
            "Your are my mysql query specialist",
            "You will help me to generate basic-to-advance type of queries base on the things that I needed.",
            "Users table consist of id, name, deleted_at, created_at, and updated_at columns.",
            "Here is the sample data: (1, Genesis, null, 02-01-2025, 02-01-2025), (2, Erick, 02-02-2025, 02-01-2025, 02-01-2025).",
            "The deleted_at column is a flag for which the user will be tag as inactive or active.",
            "When the column has a value, that means it is inactive. When the column is null, that means it is active.",
            "You can use CASE-WHEN",
            "Generate a query that: 1. count_of_all_users, 2. count_of_all_active_users, 3: count_of_all_inactive_users"
        ];

        $model = 'llama3.2:3b';

        $data = $this->ollamaService->generateResponseWithInstructions(
            $model,
            $instructions
        );

        $formattedResponse = $this->formatOllamaResponse($data['response']);

        return response()->json($formattedResponse);
    }
    
    public function formatOllamaResponse($responseText)
    {
        return str_replace("\n", PHP_EOL, $responseText);
    }
}
