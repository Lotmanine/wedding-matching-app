<?php

declare(strict_types=1);

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;

class ApiResponse
{
    public static function success(mixed $data, int $status = 200): JsonResponse
    {
        return response()->json([
            'data' => $data,
            'meta' => self::meta(),
        ], $status);
    }

    public static function error(string $code, string $message, array $details = [], int $status = 422): JsonResponse
    {
        return response()->json([
            'error' => [
                'code'    => $code,
                'message' => $message,
                'details' => $details,
            ],
            'meta' => self::meta(),
        ], $status);
    }

    private static function meta(): array
    {
        return [
            'timestamp'   => now()->toAtomString(),
            'api_version' => 'v1',
        ];
    }
}
