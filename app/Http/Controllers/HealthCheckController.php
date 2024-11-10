<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class HealthCheckController extends Controller
{
    public function status(): JsonResponse
    {
        return response()->json(['status' => 'ok'], Response::HTTP_OK);
    }
}
