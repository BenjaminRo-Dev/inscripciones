<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

use function Illuminate\Log\log;

class ChoqueHorarioException extends Exception
{
    protected $message = 'Choque de horarios';

    public function render(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $this->getMessage(),
            'data' => null,
            'code' => 409
        ], 409); //Conflicto de estado
    }

}
