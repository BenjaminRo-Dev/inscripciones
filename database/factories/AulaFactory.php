<?php

namespace Database\Factories;

use App\Models\Aula;
use App\Models\Modulo;
use Illuminate\Database\Eloquent\Factories\Factory;

class AulaFactory extends Factory
{
    protected $model = Aula::class;

    public function definition(): array
    {
        return [
            'numero' => $this->faker->bothify('AULA-###'),
            'modulo_id' => Modulo::factory(),
        ];
    }
}
