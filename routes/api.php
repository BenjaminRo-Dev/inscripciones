<?php

use App\Http\Controllers\EstadoController;
use App\Http\Controllers\InscripcionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json(['message' => 'API INSCRIPCIONES EN FUNCIONAMIENTO']);
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::apiResource('inscripciones', InscripcionController::class);


Route::get('estado/{uuid}', [EstadoController::class, 'consultarEstado']);