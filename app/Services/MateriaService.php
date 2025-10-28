<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MateriaService
{
    protected string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('services.materia.base_url');
    }

    public function obtenerMateria(int $id): ?array
    {
        try {
            $response = Http::timeout(2)->get("{$this->baseUrl}/api/materia/{$id}");

            if ($response->successful()) {
                return $response->json();
            }

            Log::warning("MateriaService: respuesta NO exitosa ({$response->status()}) al obtener materia {$id}");
        } catch (\Throwable $e) {
            Log::warning("MateriaService: error al obtener materia {$id} - {$e->getMessage()}");
        }

        return null;
    }
}
