<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CacheResponse
{
    public function handle(Request $request, Closure $next, $ttl = 3600)
    {
        // Solo cachear GET requests
        if ($request->method() !== "GET") {
            return $next($request);
        }

        $key = "api_cache_" . md5($request->fullUrl());
        
        return Cache::remember($key, $ttl, function () use ($next, $request) {
            return $next($request);
        });
    }
}