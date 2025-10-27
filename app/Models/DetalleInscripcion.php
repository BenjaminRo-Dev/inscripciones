<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetalleInscripcion extends Model
{
    use HasFactory;
    
    protected $table = 'detalle_inscripciones';
    protected $fillable = ['inscripcion_id', 'grupo_id'];

    // Una inscripción pertenece a un estudiante
    // public function grupo()
    // {
    //     return $this->belongsTo(Grupo::class, 'grupo_id');
    // }

    // Una inscripción pertenece a una gestión
    public function inscripcion()
    {
        return $this->belongsTo(Inscripcion::class, 'inscripcion_id');
    }
}
