<?php

namespace Database\Factories;

use App\Models\DetalleInscripcion;
use App\Models\Inscripcion;
use App\Models\Grupo;
use Illuminate\Database\Eloquent\Factories\Factory;

class DetalleInscripcionFactory extends Factory
{
    protected $model = DetalleInscripcion::class;

    public function definition(): array
    {
        return [
            'inscripcion_id' => Inscripcion::factory(),
            'grupo_id' => Grupo::factory(),
        ];
    }
}
