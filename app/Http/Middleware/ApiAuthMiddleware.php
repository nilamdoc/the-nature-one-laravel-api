<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\UserToken;
use App\Http\Resources\ApiResponse;

class ApiAuthMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        try {

            // 🔹 1. Check token exists
            $token = $request->bearerToken();

            if (!$token) {
                return ApiResponse::unauthorized('Token not provided');
            }

            // 🔹 2. Check token exists in DB
            $exists = UserToken::where('token', $token)->exists();

            if (!$exists) {
                return ApiResponse::unauthorized('Invalid token');
            }

            // 🔹 3. Validate JWT token
            $user = JWTAuth::setToken($token)->authenticate();

            if (!$user) {
                return ApiResponse::unauthorized('User not found');
            }

            // 🔹 4. Attach user to request (important)
            $request->merge(['auth_user' => $user]);

            return $next($request);

        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return ApiResponse::unauthorized('Token expired');

        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return ApiResponse::unauthorized('Token invalid');

        } catch (\Exception $e) {
            return ApiResponse::exception($e);
        }
    }
}