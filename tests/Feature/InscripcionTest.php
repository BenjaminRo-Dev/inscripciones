<?php

use App\Models\Estudiante;
use App\Models\Grupo;
use App\Models\Materia;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use App\Jobs\CrudJob;
use App\Models\Inscripcion;
use App\Services\InscripcionService;

uses(RefreshDatabase::class);

test('flujo completo de inscripcion', function () {
    Queue::fake();

    //Datos de pruebas
    $materia = Materia::factory()->create();
    $grupo = Grupo::factory()->create([
        'materia_id' => $materia->id,
        'cupo' => 5,
    ]);
    $estudiante = Estudiante::factory()->create();

    //prueba de solciitud de inscripcion
    $respuesta = $this->postJson('/api/inscripciones', [
        'estudiante_id' => $estudiante->id,
        'gestion_id' => 1,
        'fecha' => now()->toDateString(),
        'grupos' => [$grupo->id],
    ]);

    $respuesta->assertStatus(202)
             ->assertJsonStructure(['message', 'url', 'transaction_id', 'status']);


    // verificacion que el job fue encolado
    $transactionId = $respuesta->json('transaction_id');

    Queue::assertPushed(CrudJob::class, function ($job) use ($transactionId) {
        return $job->uuid === $transactionId;
    });

    //verificacion que la solicitud este en el cache
    $cacheData = Cache::get("t:$transactionId");
    expect($cacheData['estado'])->toBe('procesando');

    //Ejecucion del job
    $job = new CrudJob(InscripcionService::class, 'guardar', [array_merge(['uuid' => $transactionId], [
        'estudiante_id' => $estudiante->id,
        'gestion_id' => 1,
        'fecha' => now()->toDateString(),
        'grupos' => [$grupo->id],
    ])], $transactionId);

    $job->handle();

    //verificacion del cache despues de ejecutar el job
    $cacheData = Cache::get("t:$transactionId");
    expect($cacheData['estado'])->toBe('procesado');

    // verificacion de la inscripcion en la bd
    $inscripcionData = $cacheData['datos']['data'];
    $inscripcion = Inscripcion::find($inscripcionData['id']);
    expect($inscripcion)->not()->toBeNull();
    expect($inscripcion->estudiante_id)->toBe($estudiante->id);

    //Verificar que el cupo disminuyo
    $grupo->refresh();
    expect($grupo->cupo)->toBe(4);

});


// test('falla la inscripción cuando no hay cupos disponibles', function () {
//     $materia = Materia::factory()->create();
//     $grupo = Grupo::factory()->create([
//         'materia_id' => $materia->id,
//         'cupo' => 0,
//     ]);
//     $estudiante = Estudiante::factory()->create();

//     $respuesta = $this->postJson('/api/inscripciones', [
//         'estudiante_id' => $estudiante->id,
//         'grupo_id' => $grupo->id,
//     ]);

//     $respuesta->assertStatus(422)
//              ->assertJson(['error' => 'No hay cupos disponibles']);

//     expect(Inscripcion::count())->toBe(0);
// });
