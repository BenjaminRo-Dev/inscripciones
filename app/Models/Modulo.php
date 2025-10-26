<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Modulo extends Model
{
    protected $fillable = ['numero'];

    //Un modulo tiene muchas aulas:
    public function aulas()
    {
        return $this->hasMany(Aula::class);
    }
}
