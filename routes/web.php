<?php

use App\Http\Controllers\GeminiController;
use App\Http\Controllers\PromptController;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });

// Route::get('/gemini', [GeminiController::class, 'index']);
Route::get('/', [GeminiController::class, 'index']);
Route::get('/new', [GeminiController::class, 'newPage']);

Route::post('/save-prompt', [PromptController::class, 'storePrompt']);

Route::post('/generate-gemini', [GeminiController::class, 'generate']);
