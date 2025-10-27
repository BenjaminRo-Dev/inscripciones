<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Materia extends Model
{
    use HasFactory;
    
    protected $fillable = ['sigla', 'nombre', 'creditos'];

    // Una materia tiene muchos grupos
    public function grupos()
    {
        return $this->hasMany(Grupo::class, 'materia_id');
    }

    // Una materia tiene muchos prerequisitos (materias que son prerequisitos de esta)
    public function prerequisitos()
    {
        return $this->belongsToMany(Materia::class, 'prerequisitos', 'materia_id', 'prerequisito_id');
    }

    // Una materia es prerequisito de muchas materias
    public function esPrerequisitoDe()
    {
        return $this->belongsToMany(Materia::class, 'prerequisitos', 'prerequisito_id', 'materia_id');
    }

    // Una materia pertenece a muchos planes de estudio
    public function materiaPlan()
    {
        return $this->hasMany(MateriaPlan::class, 'materia_id');
    }
}
