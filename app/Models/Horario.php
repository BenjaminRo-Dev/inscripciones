<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Horario extends Model
{
    use HasFactory;
    
    protected $table = 'horarios';
    protected $fillable = ['dia', 'hora_inicio', 'hora_fin', 'grupo_id'];

    // Un horario pertenece a un grupo
    public function grupo()
    {
        return $this->belongsTo(Grupo::class, 'grupo_id');
    }

    // Un horario pertenece a un aula
    public function aula()
    {
        return $this->belongsTo(Aula::class, 'aula_id');
    }


}
