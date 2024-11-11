<?php

namespace App\Helpers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RateLimitHelper
{
    public static function rateLimitResponse(Request $request): JsonResponse
    {
        $retryAfter = $request->headers->get('Retry-After');

        return response()->json([
            'message' => 'Too many requests. Please wait before trying again.',
            'retry_after' => $retryAfter ? $retryAfter.' seconds' : 'Please wait a moment and try again.',
        ], Response::HTTP_TOO_MANY_REQUESTS)->header('Retry-After', $retryAfter);
    }
}
