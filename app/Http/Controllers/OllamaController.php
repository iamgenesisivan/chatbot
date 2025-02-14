<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Services\OllamaService;

class OllamaController extends Controller
{
    protected $ollamaService;

    public function __construct(OllamaService $ollamaService)
    {
        $this->ollamaService = $ollamaService;
    }

    public function ask(Request $request)
    {
        // $request->validate(['prompt' => 'required|string']);

        // $response = $this->ollamaService->generateResponse($request->input('prompt'));

        // return response()->json($response);

        $request->validate(['prompt' => 'required|string']);

        // Get existing chat history from session
        $chatHistory = Session::get('chat_history', []);

        // Append new user message
        $chatHistory[] = ['role' => 'user', 'content' => $request->input('prompt')];

        // Send full conversation history to Ollama
        $response = $this->ollamaService->generateResponse($chatHistory);

        // Append Ollama's response to history
        $chatHistory[] = ['role' => 'assistant', 'content' => $response['response']];

        // Save updated history in session
        Session::put('chat_history', $chatHistory);

        return response()->json(['response' => $response['response'], 'history' => $chatHistory]);
        
    }

    // Clear chat history
    public function clearChat()
    {
        Session::forget('chat_history');
        return response()->json(['message' => 'Chat history cleared']);
    }
}
