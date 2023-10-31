<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

trait APIResponse
{
    public function successResponse($data, int $code = ResponseAlias::HTTP_OK): Response
    {
        return response(['data' => $data, 'code' => $code])->header('Content-Type', 'application/json');
    }

    public function errorResponse($message, $code): JsonResponse
    {
        return response()->json(['message' => $message, 'code' => $code], $code);
    }

    public function errorMessage($message, $code): Response
    {
        return response($message, $code)->header('Content-Type', 'application/json');
    }
}
