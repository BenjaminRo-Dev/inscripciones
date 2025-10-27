<?php

namespace App\Services\Validators;

use App\Models\Horario;
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
            $response = Horario::where('grupo_id', $grupoId)->get();
            $gruposHorarios[$grupoId] = $response->map(fn($h) => [
                'dia'        => $h->dia,
                'horaInicio' => $h->hora_inicio,
                'horaFin'    => $h->hora_fin,
            ])->toArray();
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