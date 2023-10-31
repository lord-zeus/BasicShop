<?php
namespace App\Traits;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

trait APIResponse {
    /**
     * @param $data
     * @param int $code
     * @return Response
     */
    public function successResponse($data, int $code = ResponseAlias::HTTP_OK): Response
    {
        return response(['data' => $data, 'code' => $code])->header('Content-Type', 'application/json');
    }

    /**
     * @param $message
     * @param $code
     * @return JsonResponse
     *
     */
    public function errorResponse($message, $code): JsonResponse
    {
        return response()->json(['message' => $message, 'code' => $code], $code);
    }

    /**
     * @param $message
     * @param $code
     * @return Response
     *
     */
    public function errorMessage($message, $code): Response
    {
        return response($message, $code)->header('Content-Type', 'application/json');
    }


}
