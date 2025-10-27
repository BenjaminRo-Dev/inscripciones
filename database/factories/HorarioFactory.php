<?php

namespace Database\Factories;

use App\Models\Horario;
use App\Models\Grupo;
use App\Models\Aula;
use App\Models\Modulo;
use Illuminate\Database\Eloquent\Factories\Factory;

class HorarioFactory extends Factory
{
    protected $model = Horario::class;

    public function definition(): array
    {
        $horaInicio = $this->faker->time('H:i:s');
        
        return [
            'dia' => $this->faker->randomElement(['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado']),
            'hora_inicio' => $horaInicio,
            'hora_fin' => date('H:i:s', strtotime($horaInicio . ' +2 hours')),
            'grupo_id' => Grupo::factory(),
            'aula_id' => Aula::factory(),
            'modulo_id' => Modulo::factory(),
        ];
    }
}
