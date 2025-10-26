<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prerequisito extends Model
{
    use HasFactory;

    protected $table = 'prerequisitos';
    protected $fillable = ['materia_id', 'prerequisito_id'];

    // Prerequisito pertenece a una materia
    public function materia()
    {
        return $this->belongsTo(Materia::class, 'materia_id');
    }

    // Prerequisito pertenece a la materia que es prerequisito
    public function prerequisito()
    {
        return $this->belongsTo(Materia::class, 'prerequisito_id');
    }
}
