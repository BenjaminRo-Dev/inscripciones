<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Estudiante extends Model
{
    use HasFactory;
    
    protected $table = 'estudiantes';
    protected $fillable = ['registro', 'nombre', 'email', 'telefono', 'user_id', 'plan_estudio_id'];

    //Un estudiante esta en 1 plan de estudio:
    // public function planEstudio()
    // {
    //     return $this->belongsTo(PlanEstudio::class, 'plan_estudio_id');
    // }

    // Un estudiante puede estar inscrito en muchas gestiones
    public function inscripciones()
    {
        return $this->hasMany(Inscripcion::class, 'estudiante_id');
    }

    public function grupoEstudiantes()
    {
        return $this->hasMany(GrupoEstudiante::class, 'estudiante_id');
    }


    // public function user()
    // {
    //     return $this->belongsTo(User::class, 'user_id');
    // }
}