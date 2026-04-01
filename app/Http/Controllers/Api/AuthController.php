<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Resources\ApiResponse;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * 🔹 REGISTER
     */
    public function register(Request $request)
    {
        try {
            $data = $request->validate([
                'name'     => 'required|string|max:255',
                'email'    => 'required|string|email|max:255|unique:users,email',
                'password' => 'required|string|min:6|confirmed',
            ]);

            $data['password'] = Hash::make($data['password']);

            $user = User::create($data);

            return ApiResponse::success($user, 'User registered successfully');

        } catch (ValidationException $e) {

            return ApiResponse::error(
                'Validation failed',
                ['error' => implode(', ', $e->validator->errors()->all())],
                422
            );

        } catch (\Exception $e) {

            return ApiResponse::error(
                'Registration failed',
                ['error' => $e->getMessage()],
                500
            );
        }
    }

    /**
     * 🔹 LOGIN
     */
    public function login(Request $request)
    {
        try {
            $request->validate([
                'email'    => 'required|string|email',
                'password' => 'required|string|min:6',
            ]);

            // 🔥 Find user manually (MongoDB)
            $user = User::where('email', $request->email)->first();

            if (!$user || !\Hash::check($request->password, $user->password)) {
                return ApiResponse::error('Invalid email or password', [], 401);
            }

            // 🔥 Generate token manually
            $token = JWTAuth::fromUser($user);
            
            // Remove old tokens (optional)
            UserToken::where('user_id', (string) $user->_id)->delete();

            // Save token
            UserToken::create([
                'user_id' => $user->id,
                'token'   => $token
            ]);

            return ApiResponse::success([
                'access_token' => $token,
                'token_type'   => 'bearer',
                'expires_in'   => auth()->factory()->getTTL() * 60,
                'user' => [
                    'id'    => (string) $user->_id,
                    'name'  => $user->name,
                    'email' => $user->email
                ]
            ], 'Login successful');

        } catch (\Illuminate\Validation\ValidationException $e) {

            return ApiResponse::error(
                'Login failed',
                ['error' => implode(', ', $e->validator->errors()->all())],
                422
            );

        } catch (\Exception $e) {

            return ApiResponse::error(
                'Login failed',
                ['error' => $e->getMessage()],
                500
            );
        }
    }

    /**
     * 🔹 LOGOUT
     */
    public function logout(Request $request)
    {
        try {
            $token = $request->bearerToken();

            if (!$token) {
                return ApiResponse::error(
                    'Token not provided',
                    [],
                    400
                );
            }

            $user = $request->get('auth_user');

            // Remove token from DB
            UserToken::where('user_id', $user->id)
                ->where('token', $token)
                ->delete();

            // Invalidate JWT
            JWTAuth::invalidate($token);

            return ApiResponse::success([], 'Logged out successfully');

        } catch (\Exception $e) {

            return ApiResponse::error(
                'Logout failed',
                ['error' => $e->getMessage()],
                500
            );
        }
    }

    /**
     * 🔹 PROFILE (Protected)
     */
    public function profile()
    {
        try {
            $user = $request->get('auth_user');

            return ApiResponse::success($user, 'Profile fetched');

        } catch (\Exception $e) {

            return ApiResponse::error(
                'Unable to fetch profile',
                ['error' => $e->getMessage()],
                500
            );
        }
    }

    /**
     * 🔹 VERIFY TOKEN (Important)
     */
    public function verifyToken(Request $request)
    {
        try {
            $token = $request->bearerToken();

            if (!$token) {
                return ApiResponse::error(
                    'Token missing',
                    [],
                    400
                );
            }

            // Check if token exists in DB
            $exists = UserToken::where('token', $token)->exists();

            if (!$exists) {
                return ApiResponse::error(
                    'Invalid token',
                    [],
                    401
                );
            }

            // Authenticate token
            $user = JWTAuth::setToken($token)->authenticate();

            return ApiResponse::success([
                'user' => $user
            ], 'Token is valid');

        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {

            return ApiResponse::error('Token expired', [], 401);

        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {

            return ApiResponse::error('Token invalid', [], 401);

        } catch (\Exception $e) {

            return ApiResponse::error(
                'Token verification failed',
                ['error' => $e->getMessage()],
                500
            );
        }
    }
}