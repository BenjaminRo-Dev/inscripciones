<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inscripcion extends Model
{
    use HasFactory;
    
    protected $table = 'inscripciones';
    protected $fillable = ['fecha', 'estudiante_id', 'gestion_id'];

    protected $casts = [
        'fecha' => 'date',
    ];

    // Una inscripción pertenece a un estudiante
    public function estudiante()
    {
        return $this->belongsTo(Estudiante::class, 'estudiante_id');
    }

    // // Una inscripción pertenece a una gestión
    public function gestion()
    {
        return $this->belongsTo(Gestion::class, 'gestion_id');
    }

    // Una inscripción tiene un detalle de grupos inscritos
    public function detalle()
    {
        return $this->hasMany(DetalleInscripcion::class);
    }
}