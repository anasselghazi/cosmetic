<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\AuthController;


    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login',    [AuthController::class, 'login']);
    Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) { 
        return $request->user(); 
    });
    Route::post('/orders', [OrderController::class, 'store']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/mes-commandes', [OrderController::class, 'mesCommandes']);
    Route::post('/orders/{id}/cancel', [OrderController::class, 'cancel']);
});