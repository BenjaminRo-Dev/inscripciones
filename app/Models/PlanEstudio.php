<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlanEstudio extends Model
{
	protected $table = 'planes_estudio';
	protected $fillable = ['codigo', 'cantidad_semestres', 'vigente', 'carrera_id'];

	// Un plan de estudio pertenece a una carrera
	public function carrera()
	{
		return $this->belongsTo(Carrera::class, 'carrera_id');
	}

    // Un plan de estudio tiene muchas materias
	public function materiaPlanes()
	{
		return $this->hasMany(MateriaPlan::class, 'plan_estudio_id');
	}

    //En un plan de estudio estan muchos estudiantes:
    public function estudiantes()
    {
        return $this->hasMany(Estudiante::class, 'plan_estudio_id');
    }
}
