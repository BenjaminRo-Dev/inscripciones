<?php

use App\Http\Controllers\ColaController;
use App\Http\Controllers\EstadoController;
use App\Http\Controllers\GrupoController;
use App\Http\Controllers\InscripcionController;
use App\Http\Controllers\ModuloController;
use App\Http\Controllers\RabbitMQController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json(['message' => 'API INSCRIPCIONES EN FUNCIONAMIENTO']);
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::apiResource('inscripciones', InscripcionController::class);
Route::post('inscripcion-parcial', [InscripcionController::class, 'guardarParcial']);


Route::get('estado/{uuid}', [EstadoController::class, 'consultarEstado']);


//Colas e hilos:
Route::post('colas/', [ColaController::class, 'asignarWorkers']);
// Route::post('colas/estado', [ColaController::class, 'estado']);
Route::get('colas/estado-hilos', [ColaController::class, 'estadoHilos']);
Route::post('colas/estado-un-hilo', [ColaController::class, 'cambiarEstadoHilo']);
Route::post('colas/eliminar', [ColaController::class, 'eliminarCola']);

Route::get('/rabbitmq/info-colas', [RabbitMQController::class, 'getInfoColas']);
Route::get('/rabbitmq/longitudes-colas', [RabbitMQController::class, 'getLongitudesColas']);
Route::post('/rabbitmq/crear-cola', [RabbitMQController::class, 'crearCola']);

//Sincrono
Route::get('/modulos', [ModuloController::class, 'index']);
Route::get('/modulos/{id}', [ModuloController::class, 'show']);

Route::get('/grupos', [GrupoController::class, 'index']);
Route::get('/grupos/historial/{estudianteId}', [GrupoController::class, 'historialEstudiante']);
Route::get('/grupos/materias-pendientes/{estudianteId}', [GrupoController::class, 'materiasPendientes']);
Route::get('/grupos/materias-inscribibles/{estudianteId}', [GrupoController::class, 'materiasInscribibles']);
