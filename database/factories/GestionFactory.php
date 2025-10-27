<?php

namespace Database\Factories;

use App\Models\Gestion;
use Illuminate\Database\Eloquent\Factories\Factory;

class GestionFactory extends Factory
{
    protected $model = Gestion::class;

    public function definition(): array
    {
        return [
            'ano' => $this->faker->numberBetween(2020, 2025),
            'periodo' => $this->faker->randomElement([1, 2]),
        ];
    }
}
