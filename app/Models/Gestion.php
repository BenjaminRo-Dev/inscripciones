<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Gestion extends Model
{
    use HasFactory;
    
    protected $table = 'gestiones';
    protected $fillable = ['ano', 'periodo'];

    // Una gestión tiene muchas inscripciones
    public function inscripciones()
    {
        return $this->hasMany(Inscripcion::class, 'gestion_id');
    }

    // Una gestión tiene muchos grupos
    public function grupos()
    {
        return $this->hasMany(Grupo::class, 'gestion_id');
    }
}