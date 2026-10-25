<?php

namespace App\Http\Controllers;

use App\Models\ChatMessage;
use App\Models\ChatSession;
use App\Models\SiteSetting;
use App\Services\Chatbot\ChatResponder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ChatBotController extends Controller
{
    private ChatResponder $responder;

    public function __construct(ChatResponder $responder)
    {
        $this->responder = $responder;
    }

    public function send(Request $request)
    {
        if (SiteSetting::getVal('chat_enabled', '1') === '0') {
            return response()->json(['error' => 'Fitur chat sedang dinonaktifkan.'], 403);
        }

        $validated = $request->validate([
            'session_token' => 'nullable|uuid',
            'message' => 'required|string|max:1000',
        ]);

        $message = trim(strip_tags($validated['message']));
        if ($message === '') {
            return response()->json(['error' => 'Pesan tidak boleh kosong.'], 422);
        }

        $token = $validated['session_token'] ?? (string) Str::uuid();

        $session = ChatSession::firstOrCreate(
            ['session_token' => $token],
            ['created_at' => now()]
        );

        ChatMessage::create([
            'chat_session_id' => $session->id,
            'role' => 'user',
            'content' => $message,
            'created_at' => now(),
        ]);

        $reply = $this->responder->respond($session, $message);

        ChatMessage::create([
            'chat_session_id' => $session->id,
            'role' => 'assistant',
            'content' => $reply,
            'created_at' => now(),
        ]);

        return response()->json([
            'session_token' => $token,
            'reply' => $reply,
        ]);
    }

    public function history(Request $request)
    {
        $validated = $request->validate(['session_token' => 'required|uuid']);

        $session = ChatSession::where('session_token', $validated['session_token'])->first();

        if (! $session) {
            return response()->json(['messages' => []]);
        }

        $messages = $session->messages()
            ->latest('id')
            ->limit(50)
            ->get(['role', 'content'])
            ->reverse()
            ->values();

        return response()->json(['messages' => $messages]);
    }
}
