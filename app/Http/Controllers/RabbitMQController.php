<?php

namespace App\Http\Controllers;

use App\Services\RabbitMQService;
use Illuminate\Http\Request;

class RabbitMQController extends Controller
{

    public function __construct(protected RabbitMQService $rabbitMQService)
    {
        // parent::__construct();
        $this->rabbitMQService = $rabbitMQService;
    }

    public function getInfoColas()
    {
        // return "asd";
        return $this->rabbitMQService->getInfoColas();
    }

    public function getLongitudesColas()
    {
        return $this->rabbitMQService->getLongitudesColas();
    }

    public function crearCola(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'vhost' => 'nullable|string',
            'params' => 'nullable|array',
        ]);

        $this->rabbitMQService->crearCola(
            $validated['name'],
            $validated['vhost'] ?? '/',
            $validated['params'] ?? []
        );

        return response()->json(['message' => 'Cola creada.'], 201);
    }
}