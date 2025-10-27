<?php

namespace Database\Factories;

use App\Models\Tipo;
use Illuminate\Database\Eloquent\Factories\Factory;

class TipoFactory extends Factory
{
    protected $model = Tipo::class;

    public function definition(): array
    {
        return [
            'nombre' => $this->faker->randomElement(['Teórica', 'Práctica', 'Laboratorio', 'Taller']),
        ];
    }
}
