<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Aula extends Model
{
    use HasFactory;
    
    protected $table = 'aulas';
    protected $fillable = ['numero', 'modulo_id'];

    // Un aula pertenece a un módulo
    public function modulo()
    {
        return $this->belongsTo(Modulo::class, 'modulo_id');
    }

    // Un aula puede tener muchos horarios
    public function horarios()
    {
        return $this->hasMany(Horario::class, 'aula_id');
    }
}
