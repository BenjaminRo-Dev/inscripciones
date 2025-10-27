<?php

namespace Database\Factories;

use App\Models\GrupoEstudiante;
use App\Models\Estudiante;
use App\Models\Grupo;
use Illuminate\Database\Eloquent\Factories\Factory;

class GrupoEstudianteFactory extends Factory
{
    protected $model = GrupoEstudiante::class;

    public function definition(): array
    {
        return [
            'nota' => $this->faker->optional()->randomFloat(1, 0, 100),
            'creditos' => $this->faker->numberBetween(2, 8),
            'estudiante_id' => Estudiante::factory(),
            'grupo_id' => Grupo::factory(),
        ];
    }
}
