<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MateriaEstudiante extends Model
{
    use HasFactory;

    protected $table = 'materia_estudiante';

    protected $fillable = [
        'nota',
        'creditos',
        'materia_id',
        'estudiante_id',
        'grupo_id',
    ];

    protected $casts = [
        'nota' => 'decimal:1',
    ];

    public function materia()
    {
        return $this->belongsTo(Materia::class);
    }

    public function estudiante()
    {
        return $this->belongsTo(Estudiante::class);
    }

    public function grupo()
    {
        return $this->belongsTo(Grupo::class);
    }
}
