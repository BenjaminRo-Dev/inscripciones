<?php

namespace Database\Factories;

use App\Models\MateriaEstudiante;
use App\Models\Materia;
use App\Models\Estudiante;
use App\Models\Grupo;
use Illuminate\Database\Eloquent\Factories\Factory;

class MateriaEstudianteFactory extends Factory
{
    protected $model = MateriaEstudiante::class;

    public function definition(): array
    {
        return [
            'nota' => $this->faker->optional()->randomFloat(1, 0, 100),
            'creditos' => $this->faker->numberBetween(2, 8),
            'materia_id' => Materia::factory(),
            'estudiante_id' => Estudiante::factory(),
            'grupo_id' => Grupo::factory(),
        ];
    }
}
