<?php

namespace Database\Factories;

use App\Models\Carrera;
use App\Models\Facultad;
use Illuminate\Database\Eloquent\Factories\Factory;

class CarreraFactory extends Factory
{
    protected $model = Carrera::class;

    public function definition(): array
    {
        return [
            'codigo' => strtoupper($this->faker->bothify('CAR-###')),
            'nombre' => $this->faker->words(3, true),
            'facultad_id' => Facultad::factory(),
        ];
    }
}
