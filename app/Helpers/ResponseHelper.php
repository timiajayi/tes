<?php

namespace App\Helpers;

class ResponseHelper
{
    public static function success($message, $data = [], $statusCode = 200)
    {
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $data,
        ], $statusCode);
    }

    public static function error($message, $statusCode = 400)
    {
        $status = match($statusCode) {
            400 => 'Bad Request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not Found',
            500 => 'Server Error',
            default => 'Error',
        };

        return response()->json([
            'status' => $status,
            'message' => $message,
            'statusCode' => $statusCode,
        ], $statusCode);
    }
}
