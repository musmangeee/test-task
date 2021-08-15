<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

trait ApiResponse 
{
    public function success($message, $data = [], $status = 200)
    {
        return response([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $status);
    }

    public function singleSuccessResponse($message, $status = 200)
    {
        return response([
            'success' => true,
            'message' => $message,
        ], $status);
    }

    public function failure($message, $status = 422)
    {
        return response([
            'success' => false,
            'message' => $message,
        ], $status);
    }
}