<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class VerifyServiceApiKey
{
    public function handle(Request $request, Closure $next)
    {
        $apiKey = $request->header('X-Service-Api-Key');
        $validKey = config('app.service_api_key');

        if (!$validKey || $apiKey !== $validKey) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid or missing service API key.',
            ], 401);
        }

        return $next($request);
    }
}
