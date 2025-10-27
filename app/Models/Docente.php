<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Docente extends Model
{
    protected $table = 'docentes';
    protected $fillable = ['registro', 'codigo', 'nombre', 'email', 'telefono'];

    // Un docente puede tener muchos grupos
    public function grupos()
    {
        return $this->hasMany(Grupo::class, 'docente_id');
    }
}
