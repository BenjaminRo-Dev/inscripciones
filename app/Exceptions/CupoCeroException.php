<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class CupoCeroException extends Exception
{
    protected $message = 'cupo cero';

    public function render(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $this->getMessage(),
            'data' => null,
        ], 422); //Validacion fallida, prerequisitos no cumplidos
    }

}
