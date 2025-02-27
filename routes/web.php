<?php

use App\Http\Controllers\GeminiController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/gemini', [GeminiController::class, 'index']);

Route::post('/generate-gemini', [GeminiController::class, 'generate']);
