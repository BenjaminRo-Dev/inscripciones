<?php

use App\Models\Grupo;
use App\Services\Validators\CupoValidator;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('valida cupos cuando hay cupos disponibles', function () {
    $grupo1 = Grupo::factory()->create(['cupo' => 5]);
    $grupo2 = Grupo::factory()->create(['cupo' => 10]);

    $validator = new CupoValidator();
    $result = $validator->validarCupos([$grupo1->id, $grupo2->id]);

    expect($result)->toBeTrue();
});

test('no valida cupos cuando no hay cupos disponibles', function () {
    $grupo1 = Grupo::factory()->create(['cupo' => 0]);
    $grupo2 = Grupo::factory()->create(['cupo' => 5]);

    $validator = new CupoValidator();
    $result = $validator->validarCupos([$grupo1->id, $grupo2->id]);

    expect($result)->toBeFalse();
});

test('valida cupos cuando hay exactamente el último cupo disponible', function () {
    $grupo1 = Grupo::factory()->create(['cupo' => 1]);
    $grupo2 = Grupo::factory()->create(['cupo' => 1]);

    $validator = new CupoValidator();
    $result = $validator->validarCupos([$grupo1->id, $grupo2->id]);

    expect($result)->toBeTrue();
});
