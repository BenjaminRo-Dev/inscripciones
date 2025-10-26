<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MateriaPlan extends Model
{
    //Materia plan es el modelo que representa la relación entre las materias y los planes de estudio

    protected $table = 'materia_planes';
    protected $fillable = ['materia_id', 'plan_estudio_id'];

    public function materia()
    {
        return $this->belongsTo(Materia::class);
    }

    public function planEstudio()
    {
        return $this->belongsTo(PlanEstudio::class);
    }

}
