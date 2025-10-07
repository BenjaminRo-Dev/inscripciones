<?php

namespace App\Http\Controllers;

use App\Services\ColaService;
use App\Services\InscripcionService;
use Illuminate\Http\Request;

class InscripcionController extends Controller
{
    protected $colaService;
    protected $service;

    public function __construct(ColaService $colaService, InscripcionService $service)
    {
        // parent::__construct();
        $this->colaService = $colaService;
        $this->service = $service;
    }

    public function index()
    {
        return $this->colaService->encolar(InscripcionService::class, 'mostrarTodos');
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'estudiante_id' => ['required', 'integer'],
            'gestion_id'    => ['required', 'integer'],
            'fecha'         => ['required', 'date'],
            'grupos'        => ['required', 'array', 'min:1'],
            'grupos.*'      => ['integer'],
        ]);

        $this->colaService->encolar(InscripcionService::class, 'guardar', $datos);
            
    }

    public function show(string $id)
    {
        return $this->colaService->encolar(InscripcionService::class, 'mostrar', $id);
    }

    public function update(Request $request, string $id)
    {
        $datos = $request->validate([
            'estudiante_id' => ['sometimes', 'required', 'integer'],
            'gestion_id'    => ['sometimes', 'required', 'integer'],
            'fecha'         => ['sometimes', 'required', 'date'],
            'grupos'        => ['sometimes', 'required', 'array', 'min:1'],
            'grupos.*'      => ['integer'],
        ]);

        return $this->colaService->encolar(InscripcionService::class, 'actualizar', $datos, $id);
    }

    public function destroy(string $id)
    {
        return $this->colaService->encolar(InscripcionService::class, 'eliminar', $id);
    }
}
