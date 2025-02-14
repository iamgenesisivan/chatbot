<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\ChatHistory;
use Illuminate\Support\Str;

class ChatController extends Controller
{
    public function __construct()
    {
        $this->middleware('web'); // Ensures session is available
    }
    public function streamResponse(Request $request)
    {
        $sessionId = $request->session()->get('chat_session_id', Str::uuid());
        $request->session()->put('chat_session_id', $sessionId);

        $userMessage = $request->input('prompt');
        $model = 'llama3.2:3b';
        $ollamaUrl = 'http://192.168.230.3:9050/api/generate';

        // Store user message
        ChatHistory::create([
            'session_id' => $sessionId,
            'role' => 'user',
            'content' => $userMessage
        ]);

        // Retrieve chat history for context
        $history = ChatHistory::where('session_id', $sessionId)
            ->orderBy('created_at')
            ->get(['role', 'content'])
            ->map(fn ($msg) => $msg['role'] . ': ' . $msg['content'])
            ->implode("\n");

        // Set headers for streaming
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');

        $body = [
            'model' => $model,
            'prompt' => $history . "\n\nAssistant: ",
            'stream' => true,
        ];

        // Stream Ollama response
        $response = Http::withOptions(['stream' => true])
                        ->post($ollamaUrl, $body);

        if ($response->failed()) {
            echo "data: [ERROR] Failed to connect to Ollama\n\n";
            ob_flush();
            flush();
            exit();
        }

        $assistantReply = "";
        foreach ($response->toPsrResponse()->getBody() as $chunk) {
            $assistantReply .= trim($chunk);
            echo "data: " . trim($chunk) . "\n\n";
            ob_flush();
            flush();
        }

        // Store assistant reply
        ChatHistory::create([
            'session_id' => $sessionId,
            'role' => 'assistant',
            'content' => $assistantReply
        ]);

        echo "data: [DONE]\n\n";
    }

    public function getChatHistory(Request $request)
    {
        $sessionId = $request->session()->get('chat_session_id');
        if (!$sessionId) return response()->json([]);

        $history = ChatHistory::where('session_id', $sessionId)
            ->orderBy('created_at')
            ->get(['role', 'content']);

        return response()->json($history);
    }

    public function clearChatHistory(Request $request)
    {
        $sessionId = $request->session()->get('chat_session_id');
        if ($sessionId) {
            ChatHistory::where('session_id', $sessionId)->delete();
            $request->session()->forget('chat_session_id');
        }
        return response()->json(['message' => 'Chat history cleared']);
    }
}
