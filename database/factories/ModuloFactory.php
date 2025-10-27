<?php

namespace Database\Factories;

use App\Models\Modulo;
use Illuminate\Database\Eloquent\Factories\Factory;

class ModuloFactory extends Factory
{
    protected $model = Modulo::class;

    public function definition(): array
    {
        return [
            'numero' => $this->faker->bothify('MOD-#'),
        ];
    }
}
