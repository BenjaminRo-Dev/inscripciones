<?php

namespace App\Http\Controllers;

use App\Models\Modulo;
use Illuminate\Http\Request;

class ModuloController extends Controller
{
    public function index()
    {
        $modulos = Modulo::with('aulas.horarios')->get();
        return response()->json($modulos);
    }

    public function show($id)
    {
        $modulo = Modulo::with('aulas.horarios')->find($id);
        if (!$modulo) {
            return response()->json(['message' => 'Módulo no encontrado'], 404);
        }
        return response()->json($modulo);
    }
}
