<?php

namespace App\Services\Validators;

use App\Exceptions\CupoCeroException;
use App\Models\Grupo;
use Illuminate\Support\Facades\Http;

class CupoValidator
{
    public function validarCupos(array $gruposIds): bool
    {
        foreach ($gruposIds as $grupoId) {
            $grupo = Grupo::where('id', $grupoId)->lockForUpdate()->firstOrFail();
            if ($grupo->cupo <= 0) {
                // return false;
                throw new CupoCeroException("Uno o más grupos no tienen cupos disponibles.");
            }
        }
        return true;
    }
}