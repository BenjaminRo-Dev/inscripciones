<?php

namespace Database\Factories;

use App\Models\Docente;
use Illuminate\Database\Eloquent\Factories\Factory;

class DocenteFactory extends Factory
{
    protected $model = Docente::class;

    public function definition(): array
    {
        return [
            'registro' => $this->faker->unique()->numerify('########'),
            'codigo' => $this->faker->numerify('#####'),
            'nombre' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'telefono' => $this->faker->phoneNumber(),
        ];
    }
}
