<?php

namespace Database\Factories;

use App\Models\Inscripcion;
use App\Models\Estudiante;
use App\Models\Gestion;
use Illuminate\Database\Eloquent\Factories\Factory;

class InscripcionFactory extends Factory
{
    protected $model = Inscripcion::class;

    public function definition(): array
    {
        return [
            'fecha' => $this->faker->date(),
            'estudiante_id' => Estudiante::factory(),
            'gestion_id' => Gestion::factory(),
        ];
    }
}
