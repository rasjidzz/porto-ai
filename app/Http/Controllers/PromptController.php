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
            'user_id' => 'nullable|exists:users,id',
            'user_question' => 'required|string',
            'ai_response' => 'required|string',
        ]);

        $session = ChatSession::firstOrCreate(
            ['user_id' => $request->user_id],
            ['session_id' => Str::uuid()]
        );

        $prompt = Prompt::create([
            'session_id' => $session->session_id,
            'user_question' => $request->user_question,
            'ai_response' => $request->ai_response,
        ]);

        return response()->json([
            'message' => 'Prompt saved successfully',
            'session_id' => $session->session_id,
            'prompt' => $prompt,
        ]);
    }
}
