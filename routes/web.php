<?php

use App\Http\Controllers\GeminiController;
use App\Http\Controllers\PromptController;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/', [GeminiController::class, 'index']);

Route::post('/save-prompt', [PromptController::class, 'storePrompt']);

Route::post('/generate-gemini', [GeminiController::class, 'generate']);
