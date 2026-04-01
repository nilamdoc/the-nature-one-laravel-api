<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckOrigin
{
    public function handle(Request $request, Closure $next)
    {
        // ✅ Allow local API access (DEV only)
        if (in_array($request->getHost(), ['127.0.0.1', 'localhost'])) {
            return $next($request);
        }

        $allowedDomains = [
            'thenatureone.com',
            'www.thenatureone.com',
        ];

        $origin = $request->headers->get('origin');

        if (!$origin) {
            $referer = $request->headers->get('referer');
            if ($referer) {
                $origin = $referer;
            }
        }

        if (!$origin) {
            return response()->json([
                'status' => false,
                'message' => 'Direct access not allowed'
            ], 403);
        }

        $host = parse_url($origin, PHP_URL_HOST);

        if (!in_array($host, $allowedDomains)) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized domain',
                'detected_host' => $host
            ], 403);
        }

        return $next($request);
    }
}