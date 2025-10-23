<?php

namespace App\Services\Validators;

use Illuminate\Support\Facades\Http;

class HorarioValidator
{
    public function validarChoqueHorarios(array $gruposIds): array
    {
        $gruposHorarios = $this->obtenerHorarios($gruposIds);
        return $this->detectarChoques($gruposHorarios);
    }

    private function obtenerHorarios(array $gruposIds): array
    {
        $gruposHorarios = [];
        foreach ($gruposIds as $grupoId) {
            $response = Http::get("http://grupos-service:3001/api/horario/grupo/{$grupoId}");
            $gruposHorarios[$grupoId] = $response->json();
        }
        return $gruposHorarios;
    }

    private function detectarChoques(array $gruposHorarios): array
    {
        $gruposEnChoque = [];

        foreach ($gruposHorarios as $grupoIdA => $horariosA) {
            foreach ($gruposHorarios as $grupoIdB => $horariosB) {
                if ($grupoIdA >= $grupoIdB) continue;

                foreach ($horariosA as $hA) {
                    foreach ($horariosB as $hB) {
                        if ($this->hayChoque($hA, $hB)) {
                            $gruposEnChoque[] = [$grupoIdA, $grupoIdB];
                            break 3;
                        }
                    }
                }
            }
        }

        return $gruposEnChoque;
    }

    private function hayChoque(array $hA, array $hB): bool
    {
        if ($hA['dia'] !== $hB['dia']) return false;

        $inicioA = strtotime($hA['horaInicio']);
        $finA    = strtotime($hA['horaFin']);
        $inicioB = strtotime($hB['horaInicio']);
        $finB    = strtotime($hB['horaFin']);

        return $inicioA < $finB && $inicioB < $finA;
    }
}