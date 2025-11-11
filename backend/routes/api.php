<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PropostaController;


// Rotas da API de propostas
Route::get('/propostas', [PropostaController::class, 'index']);
Route::post('/propostas', [PropostaController::class, 'store']);
Route::get('/propostas/{id}', [PropostaController::class, 'show']);
Route::get('/propostas/{id}/status', [PropostaController::class, 'atualizarStatus']);
Route::patch("/propostas/{id}/status", [PropostaController::class, "atualizarStatus"]);
Route::delete('/propostas/{id}', [PropostaController::class, 'destroy']);
