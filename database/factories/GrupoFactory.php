<?php

namespace Database\Factories;

use App\Models\Grupo;
use App\Models\Materia;
use App\Models\Docente;
use App\Models\Gestion;
use Illuminate\Database\Eloquent\Factories\Factory;

class GrupoFactory extends Factory
{
    protected $model = Grupo::class;

    public function definition(): array
    {
        return [
            'sigla' => strtoupper($this->faker->bothify('GRP-??')),
            'cupo' => $this->faker->numberBetween(20, 50),
            'materia_id' => Materia::factory(),
            'docente_id' => Docente::factory(),
            'gestion_id' => Gestion::factory(),
        ];
    }
}

