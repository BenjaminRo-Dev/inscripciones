<?php

use App\Models\Estudiante;
use App\Models\Grupo;
use App\Models\Materia;
use App\Models\Inscripcion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use App\Jobs\CrudJob;
use App\Models\Aula;
use App\Models\Horario;
use App\Models\Modulo;
use App\Services\InscripcionService;

uses(RefreshDatabase::class);

test('flujo de inscripcion falla cuando no hay cupos', function () {
    Queue::fake();

    $materia = Materia::factory()->create();
    $grupo = Grupo::factory()->create([
        'materia_id' => $materia->id,
        'cupo' => 0,
    ]);
    $estudiante = Estudiante::factory()->create();

    $respuesta = $this->postJson('/api/inscripciones', [
        'estudiante_id' => $estudiante->id,
        'gestion_id' => 1,
        'fecha' => now()->toDateString(),
        'grupos' => [$grupo->id],
    ]);

    $respuesta->assertStatus(202);


    
    $transactionId = $respuesta->json('transaction_id');

    $job = new CrudJob(InscripcionService::class, 'guardar', [array_merge(['uuid' => $transactionId], [
        'estudiante_id' => $estudiante->id,
        'gestion_id' => 1,
        'fecha' => now()->toDateString(),
        'grupos' => [$grupo->id],
    ])], $transactionId);
    $job->handle();

    // Verificar que el cache muestre el error
    $cacheData = Cache::get("t:$transactionId");
    expect($cacheData['estado'])->toBe('procesado');
    // dd($cacheData);

    $estadoProceso = $cacheData['datos']['message'];
    expect($estadoProceso)->toContain('Uno o más grupos no tienen cupos disponibles');


    expect(Inscripcion::count())->toBe(0);
});

test('flujo de inscripcion falla cuando hay choque de horarios', function () {
    Queue::fake();

    $materia = Materia::factory()->create();

    $grupo1 = Grupo::factory()->create([
        'materia_id' => $materia->id,
        'cupo' => 5
    ]);
    
    Horario::factory()->create(
        [
            'dia' => 'Lunes',
            'hora_inicio' => '10:00:00',
            'hora_fin' => '12:00:00',
            'grupo_id' => $grupo1->id,
        ]
    );

    $grupo2 = Grupo::factory()->create([
        'materia_id' => $materia->id,
        'cupo' => 5,
    ]);
    
    Horario::factory()->create([
        'dia' => 'Lunes',
        'hora_inicio' => '10:00:00',
        'hora_fin' => '12:00:00',
        'grupo_id' => $grupo2->id,
    ]);

    $estudiante = Estudiante::factory()->create();



    $respuesta = $this->postJson('/api/inscripciones', [
        'estudiante_id' => $estudiante->id,
        'gestion_id' => 1,
        'fecha' => now()->toDateString(),
        'grupos' => [$grupo1->id, $grupo2->id],
    ]);

    $respuesta->assertStatus(202);

    $transactionId = $respuesta->json('transaction_id');

    $job = new CrudJob(InscripcionService::class, 'guardar', [array_merge(['uuid' => $transactionId], [
        'estudiante_id' => $estudiante->id,
        'gestion_id' => 1,
        'fecha' => now()->toDateString(),
        'grupos' => [$grupo1->id, $grupo2->id],
    ])], $transactionId);
    $job->handle();

    // Cache debe reflejar error por choque
    $cacheData = Cache::get("t:$transactionId");
    // dd($cacheData);
    expect($cacheData['estado'])->toBe('procesado');
    // dd($cacheData['datos']['message']);
    // expect($cacheData['datos']['message'])->toBe("Error al guardar la inscripción: Existen choques de horario entre los grupos: (1, 2)");
    expect($cacheData['datos']['message'])->toContain('Existen choques de horario');


    expect(Inscripcion::count())->toBe(0);
});
