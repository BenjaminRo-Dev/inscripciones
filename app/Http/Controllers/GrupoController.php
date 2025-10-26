<?php

namespace App\Http\Controllers;

use App\Models\Grupo;
use Illuminate\Http\Request;

class GrupoController extends Controller
{
    public function index()
    {
        $grupos = Grupo::with('horarios', 'horarios.aula')->get();
        return response()->json($grupos);
    }

    


}
