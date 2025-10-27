<?php

namespace Database\Factories;

use App\Models\PlanEstudio;
use App\Models\Carrera;
use Illuminate\Database\Eloquent\Factories\Factory;

class PlanEstudioFactory extends Factory
{
    protected $model = PlanEstudio::class;

    public function definition(): array
    {
        return [
            'codigo' => strtoupper($this->faker->bothify('PLAN-####')),
            'cantidad_semestres' => $this->faker->numberBetween(8, 12),
            'vigente' => $this->faker->boolean(80),
            'carrera_id' => Carrera::factory(),
        ];
    }
}
