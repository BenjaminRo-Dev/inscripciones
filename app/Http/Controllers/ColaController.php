<?php

namespace App\Http\Controllers;

use App\Services\ColaService;
use Illuminate\Http\Request;

class ColaController extends Controller
{

    public function __construct(protected ColaService $colaService)
    {
        $this->colaService = $colaService;
    }

    public function asignarWorkers(Request $request)
    {
        $request->validate([
            'cola' => 'required|string',
            'workers' => 'required|integer|min:0'
        ]);

        // return $request;

        return $this->colaService->asignarWorkers($request->cola, $request->workers);
    }

    public function estadoHilos()
    {
        return $this->colaService->estadoHilos();
    }

    public function cambiarEstadoHilo(Request $request)
    {
        $request->validate([
            'accion' => 'required|string|in:start,stop,status',
            'hilo' => 'required|string',
        ]);

        return $this->colaService->cambiarEstadoHilo($request->accion, $request->hilo);
    }

    public function eliminarCola(Request $request)
    {
        $request->validate([
            'cola' => 'required|string',
        ]);

        return $this->colaService->eliminarCola($request->cola);
    }
}
