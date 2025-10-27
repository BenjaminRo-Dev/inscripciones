<?php

namespace Database\Factories;

use App\Models\Materia;
use App\Models\Nivel;
use App\Models\Tipo;
use Illuminate\Database\Eloquent\Factories\Factory;

class MateriaFactory extends Factory
{
    protected $model = Materia::class;

    public function definition(): array
    {
        return [
            'sigla' => strtoupper($this->faker->bothify('???-###')),
            'nombre' => $this->faker->words(3, true),
            'creditos' => $this->faker->numberBetween(2, 8),
            'nivel_id' => Nivel::factory(),
            'tipo_id' => Tipo::factory(),
        ];
    }
}
