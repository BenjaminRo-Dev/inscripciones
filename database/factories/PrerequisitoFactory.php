<?php

namespace Database\Factories;

use App\Models\Prerequisito;
use App\Models\Materia;
use Illuminate\Database\Eloquent\Factories\Factory;

class PrerequisitoFactory extends Factory
{
    protected $model = Prerequisito::class;

    public function definition(): array
    {
        return [
            'materia_id' => Materia::factory(),
            'prerequisito_id' => Materia::factory(),
        ];
    }
}
