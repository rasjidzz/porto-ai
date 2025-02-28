<?php

namespace App\Http\Controllers;

use App\Models\ChatSession;
use App\Models\Prompt;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PromptController extends Controller
{
    public function storePrompt(Request $request)
    {
        $request->validate([
            // 'user_id' => 'nullable|exists:users,id',
            'user_question' => 'required|string',
            'ai_response' => 'required|string',
            'chat_session' => 'required'
        ]);

        $session = ChatSession::firstOrCreate(
            // ['chat_session_id' => Str::uuid()]
            ['chat_session_id' => $request->chat_session]
        );

        $prompt = Prompt::create([
            'chat_session_id' => $session->chat_session_id,
            'user_question' => $request->user_question,
            'ai_response' => $request->ai_response,
        ]);

        return response()->json([
            'message' => 'Prompt saved successfully',
            'session_id' => $session->chat_session_id,
            'prompt' => $prompt,
        ]);
    }
}
