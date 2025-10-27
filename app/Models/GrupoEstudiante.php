<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GrupoEstudiante extends Model
{
    use HasFactory;
    
    protected $table = 'grupo_estudiante';
    protected $fillable = ['grupo_id', 'estudiante_id', 'nota', 'creditos'];

    // Un grupo_estudiante pertenece a un grupo
    public function grupo()
    {
        return $this->belongsTo(Grupo::class, 'grupo_id');
    }

    // Un grupo_estudiante pertenece a un estudiante
    public function estudiante()
    {
        return $this->belongsTo(Estudiante::class, 'estudiante_id');
    }
}
