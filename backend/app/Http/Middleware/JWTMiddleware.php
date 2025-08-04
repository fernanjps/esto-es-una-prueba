<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class JWTMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            if (!$user) {
                return response()->json([
                    "success" => false,
                    "message" => "User not found"
                ], 404);
            }
        } catch (JWTException $e) {
            return response()->json([
                "success" => false,
                "message" => "Token invalid"
            ], 401);
        }

        return $next($request);
    }
}