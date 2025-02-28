<?php

namespace App\Http\Controllers;

use App\Services\GeminiService;
use Illuminate\Http\Request;

class GeminiController extends Controller
{
    protected $geminiService;
    public function __construct(GeminiService $geminiService)
    {
        $this->geminiService = $geminiService;
    }
    public function index()
    {
        return view('Gemini_Page.gemini');
    }
    public function newPage()
    {
        return view('New_Page.index');
    }
    public function generate(Request $request)
    {
        $request->validate(['prompt' => 'required|string']);

        $response = $this->geminiService->generateResponse($request->input('prompt'));

        return response()->json($response);
    }
}
