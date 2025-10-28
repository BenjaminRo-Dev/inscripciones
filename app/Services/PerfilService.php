<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PerfilService
{
    protected string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('services.perfil.base_url');
    }

    public function obtenerEstudiante(int $id): ?array
    {
        try {
            $response = Http::timeout(2)->get("{$this->baseUrl}/api/estudiantes/{$id}");

            if ($response->successful()) {
                return $response->json();
            }

            Log::warning("PerfilService: respuesta no exitosa ({$response->status()}) al obtener estudiante {$id}");
        } catch (\Throwable $e) {
            Log::warning("PerfilService: error al obtener estudiante {$id} - {$e->getMessage()}");
        }

        return null;
    }

    public function obtenerDocente(int $id): ?array
    {
        try {
            $response = Http::timeout(2)->get("{$this->baseUrl}/api/docentes/{$id}");

            if ($response->successful()) {
                return $response->json();
            }

            Log::warning("PerfilService: respuesta no exitosa ({$response->status()}) al obtener docente {$id}");
        } catch (\Throwable $e) {
            Log::warning("PerfilService: error al obtener docente {$id} - {$e->getMessage()}");
        }

        return null;
    }


}
