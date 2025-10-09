<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Cache;

class EstadoController extends Controller
{
    public function consultarEstado($uuid)
    {
        $estado = Cache::get("t:$uuid");

        if (!$estado) {
            return response()->json([
                'Solicitud' => 'Transacción expirada.'
            ]);
        }

        return response()->json([
            'Solicitud' => $estado
        ]);
    }
}
