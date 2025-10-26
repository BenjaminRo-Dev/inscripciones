<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Carrera extends Model
{
    protected $table = 'carreras';
    protected $fillable = ['codigo', 'nombre', 'facultad_id'];

    // Una carrera tiene muchos planes de estudio
    public function planesEstudio()
    {
        return $this->hasMany(PlanEstudio::class, 'carrera_id');
    }

    // Una carrera pertenece a una facultad
    // public function facultad()
    // {
    //     return $this->belongsTo(Facultad::class, 'facultad_id');
    // }
}
