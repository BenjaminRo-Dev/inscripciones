<?php

namespace App\Services\Repositories;

use App\Models\Inscripcion;
use App\Models\DetalleInscripcion;
use App\Models\Grupo;

class InscripcionRepository
{
    public function inscribir(array $datos): Inscripcion
    {
        $inscripcion = $this->crear($datos);
        $this->agregarDetalles($inscripcion, $datos['grupos']);
        $this->decrementarCupos($datos['grupos']);

        return $this->obtenerConRelaciones($inscripcion->id);
    }

    private function crear(array $datos): Inscripcion
    {
        return Inscripcion::create([
            'estudiante_id' => $datos['estudiante_id'],
            'gestion_id'    => $datos['gestion_id'],
            'fecha'         => $datos['fecha'],
        ]);
    }

    private function agregarDetalles(Inscripcion $inscripcion, array $gruposIds): void
    {
        foreach ($gruposIds as $grupoId) {
            DetalleInscripcion::create([
                'inscripcion_id' => $inscripcion->id,
                'grupo_id'       => $grupoId,
            ]);
        }
    }

    private function decrementarCupos(array $gruposIds): void
    {
        foreach ($gruposIds as $grupoId) {
            Grupo::findOrFail($grupoId)->decrement('cupo');
        }
    }

    private function obtenerConRelaciones(int $id): Inscripcion
    {
        return Inscripcion::with('gestion', 'estudiante', 'detalle')->findOrFail($id);
    }

    public function filtrarGruposConCupos(array $gruposIds): array
    {
        $gruposValidos = [];
        $gruposSinCupo = [];

        foreach ($gruposIds as $grupoId) {
            $grupo = Grupo::where('id', $grupoId)->lockForUpdate()->firstOrFail();
            if ($grupo->cupo > 0) {
                $gruposValidos[] = $grupoId;
            } else {
                $gruposSinCupo[] = $grupoId;
            }
        }

        return [
            'validos' => $gruposValidos,
            'sin_cupo' => $gruposSinCupo,
        ];
    }
}