<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class IsEmployee
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->user() || !in_array($request->user()->role, ['employee', 'admin'])) {
            return response()->json([
                'message' => 'Accès réservé aux employés'
            ], 403);
        }

        return $next($request);
    }
}