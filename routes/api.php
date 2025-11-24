<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\QueueApiController;

// Health Check (para o frontend saber que o backend existe)
Route::get('/health', function(){ return response()->json(['ok'=>true]); });

// Rotas Públicas
Route::get('/queues', [QueueApiController::class, 'index']);
Route::post('/tickets', [QueueApiController::class, 'storeTicket']);
Route::get('/tickets/{ext_id}', [QueueApiController::class, 'showTicket']);
Route::post('/tickets/{ext_id}/status', [QueueApiController::class, 'updateStatus']);

// Rotas de Operação (Atendimento e Monitor)
Route::get('/queues/{id}/current', [QueueApiController::class, 'getCurrent']);
Route::get('/queues/{id}/waiting', [QueueApiController::class, 'getWaiting']);
Route::post('/queues/{id}/next', [QueueApiController::class, 'callNext']);
Route::get('/monitor/{id}', [QueueApiController::class, 'monitor']);
