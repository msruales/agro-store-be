<?php

namespace App\Traits;

trait ApiResponse
{

    public function successResponse($data = null, $code = 200, $msj = 'ok'): \Illuminate\Http\JsonResponse
    {
        return response()->json(array("data" => $data, "code" => $code, "message" => $msj), $code);
    }

    public function errorResponse($data = null, $code = 500, $msj = 'fail'): \Illuminate\Http\JsonResponse
    {
        return response()->json(array("data" => $data, "code" => $code, "message" => $msj), $code);
    }
}

