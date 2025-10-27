<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Grupo extends Model
{
    use HasFactory;
    protected $table = 'grupos';
    protected $fillable = ['sigla', 'cupo', 'materia_id', 'docente_id', 'gestion_id'];

    // Un grupo pertenece a un docente
    public function docente()
    {
        return $this->belongsTo(Docente::class, 'docente_id');
    }

    // Un grupo pertenece a una gestión
    public function gestion()
    {
        return $this->belongsTo(Gestion::class, 'gestion_id');
    }

    // Un grupo puede tener muchos horarios
    public function horarios()
    {
        return $this->hasMany(Horario::class, 'grupo_id');
    }

    public function detallesInscripcion()
    {
        return $this->hasMany(DetalleInscripcion::class);
    }

    // Un grupo pertenece a una materia
    public function materia()
    {
        return $this->belongsTo(Materia::class, 'materia_id');
    }

    public function grupoEstudiantes()
    {
        return $this->hasMany(GrupoEstudiante::class, 'grupo_id');
    }
}