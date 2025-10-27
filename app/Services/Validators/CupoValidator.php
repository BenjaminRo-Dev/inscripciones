<?php

namespace App\Services\Validators;

use App\Models\Grupo;

class CupoValidator
{
    public function validarCupos(array $gruposIds): bool
    {
        foreach ($gruposIds as $grupoId) {
            $grupo = Grupo::where('id', $grupoId)->lockForUpdate()->firstOrFail();
            if ($grupo->cupo <= 0) {
                return false;
            }
        }
        return true;
    }
}