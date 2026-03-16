<?php

use Illuminate\Http\Request;
use App\Models\User ;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/test-token', function () {
    $user::where('email', 'admin@gmail.com')->first();
    $token = $user->createToken('test')->plainTextToken;
    return ['token' => $token];
});
