<?php

namespace Database\Factories;

use App\Models\Facultad;
use Illuminate\Database\Eloquent\Factories\Factory;

class FacultadFactory extends Factory
{
    protected $model = Facultad::class;

    public function definition(): array
    {
        return [
            'nombre' => $this->faker->words(3, true),
            'abreviacion' => strtoupper($this->faker->lexify('???')),
        ];
    }
}
