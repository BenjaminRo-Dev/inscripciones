<?php

namespace Database\Factories;

use App\Models\MateriaPlan;
use App\Models\Materia;
use App\Models\PlanEstudio;
use Illuminate\Database\Eloquent\Factories\Factory;

class MateriaPlanFactory extends Factory
{
    protected $model = MateriaPlan::class;

    public function definition(): array
    {
        return [
            'materia_id' => Materia::factory(),
            'plan_estudio_id' => PlanEstudio::factory(),
        ];
    }
}
