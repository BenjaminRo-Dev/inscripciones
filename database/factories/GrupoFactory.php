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
            'sigla' => strtoupper($this->faker->bothify('GRP-###')),
            'cupo' => $this->faker->numberBetween(0, 40),
            'materia_id' => '1',
            'docente_id' => '1',
            'gestion_id' => '1',
        ];
    }
}

