<?php

namespace App\Traits;

use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;

trait HasResponse
{
    //

    public function successResponse(string $message='', array $data= []):JsonResponse
    {
        return response()->json(
            [
                'status'=>'success',
                'message'=>$message,
                'data'=>$data
            ],
            Response::HTTP_OK
        );
    }

    public function badRequestResponse(string $message=''):JsonResponse
    {
        return response()->json(
            [
                'status'=>'error',
                'message'=>$message
            ],
            Response::HTTP_BAD_REQUEST
        );
    }

    public function resourceNotFoundResponse(string $message=''):JsonResponse
    {
        return response()->json(
            [
                'status'=>'error',
                'message'=>$message
            ],
            Response::HTTP_NOT_FOUND
        );
    }
}
