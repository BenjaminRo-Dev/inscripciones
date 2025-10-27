<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Modulo extends Model
{
    use HasFactory;
    
    protected $fillable = ['numero'];

    //Un modulo tiene muchas aulas:
    public function aulas()
    {
        return $this->hasMany(Aula::class);
    }
}
