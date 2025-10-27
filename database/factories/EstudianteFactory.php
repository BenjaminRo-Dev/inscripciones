<?php

namespace Database\Factories;

use App\Models\Estudiante;
use App\Models\PlanEstudio;
use Illuminate\Database\Eloquent\Factories\Factory;

class EstudianteFactory extends Factory
{
    protected $model = Estudiante::class;

    public function definition(): array
    {
        return [
            'registro' => $this->faker->unique()->numerify('########'),
            'codigo' => $this->faker->numerify('#####'),
            'nombre' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'telefono' => $this->faker->phoneNumber(),
            'plan_estudio_id' => PlanEstudio::factory(),
        ];
    }
}
