<?php

namespace App\Http\Resources;

use Illuminate\Support\MessageBag;
use Illuminate\Validation\Validator;

class ApiResponse
{
    /**
     * ✅ SUCCESS RESPONSE
     */
    public static function success($data = [], $message = 'Success', $code = 200)
    {
        return response()->json([
            'status'  => true,
            'message' => $message,
            'data'    => $data
        ], $code);
    }

    /**
     * ❌ ERROR RESPONSE (Handles all formats)
     */
    public static function error($message = 'Error', $errors = [], $code = 400)
    {
        // 🔹 If Laravel Validator instance passed
        if ($errors instanceof Validator) {
            $errors = $errors->errors();
        }

        // 🔹 If MessageBag passed
        if ($errors instanceof MessageBag) {
            $errors = $errors->toArray();
        }

        // 🔹 If string error
        if (is_string($errors)) {
            $errors = ['general' => [$errors]];
        }

        // 🔹 If indexed array (like ['msg1','msg2'])
        if (is_array($errors) && array_values($errors) === $errors) {
            $errors = ['general' => $errors];
        }

        // 🔹 If empty errors but message exists
        if (empty($errors)) {
            $errors = ['general' => [$message]];
        }

        return response()->json([
            'status'  => false,
            'message' => $message,
            'errors'  => $errors
        ], $code);
    }

    /**
     * ⚠️ VALIDATION HELPER (Direct use)
     */
    public static function validation($validator)
    {
        return self::error(
            'Validation failed',
            $validator,
            422
        );
    }

    /**
     * 🔐 UNAUTHORIZED RESPONSE
     */
    public static function unauthorized($message = 'Unauthorized')
    {
        return self::error($message, [], 401);
    }

    /**
     * 🚫 FORBIDDEN RESPONSE
     */
    public static function forbidden($message = 'Forbidden')
    {
        return self::error($message, [], 403);
    }

    /**
     * 🔍 NOT FOUND RESPONSE
     */
    public static function notFound($message = 'Resource not found')
    {
        return self::error($message, [], 404);
    }

    /**
     * 💥 EXCEPTION HANDLER (Clean output)
     */
    public static function exception($e, $message = 'Something went wrong')
    {
        return response()->json([
            'status'  => false,
            'message' => $message,
            'errors'  => [
                'exception' => [
                    config('app.debug') ? $e->getMessage() : 'Internal server error'
                ]
            ]
        ], 500);
    }
}