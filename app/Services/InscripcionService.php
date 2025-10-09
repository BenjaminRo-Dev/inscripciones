<?php

namespace App\Services;

use App\Models\DetalleInscripcion;
use App\Models\Grupo;
use App\Models\Inscripcion;
use Illuminate\Support\Facades\DB;

class InscripcionService
{   
    public function mostrar($id)
    {
        return Inscripcion::with('detalle')->findOrFail($id);
        // return Inscripcion::with([
        //     'gestion',
        //     'estudiante',
        //     'detalle',
        //     'detalle.grupo',
        //     'detalle.grupo.materia',
        //     'detalle.grupo.docente',
        //     'detalle.grupo.horarios',
        //     'detalle.grupo.horarios.modulo',
        //     'detalle.grupo.horarios.aula',
        // ])->findOrFail($id);
    }

    public function mostrarTodos()
    {
        return Inscripcion::all();
        // return Inscripcion::with('gestion', 'estudiante', 'detalle')->get();
    }

    public function guardar(array $datos)
    {
        return DB::transaction(function () use ($datos) {

            foreach ($datos['grupos'] as $grupoId) {
                $grupo = Grupo::findOrFail($grupoId);
                if ($grupo->cupo <= 0) {
                    return response()->json(['message' => "El grupo con ID $grupoId no tiene cupos disponibles."], 400);
                }
            }

            $inscripcion = Inscripcion::create([
                'estudiante_id' => $datos['estudiante_id'],
                'gestion_id'    => $datos['gestion_id'],
                'fecha'         => $datos['fecha'],
            ]);

            foreach ($datos['grupos'] as $grupoId) {
                Grupo::findOrFail($grupoId)->decrement('cupo');
            }

            foreach ($datos['grupos'] as $grupoId) {
                DetalleInscripcion::create([
                    'inscripcion_id' => $inscripcion->id,
                    'grupo_id'       => $grupoId,
                ]);
            }

            return Inscripcion::with('gestion', 'estudiante', 'detalle')->find($inscripcion->id);
        });
    }


    public function actualizar(array $datos, $id)
    {
        return DB::transaction(function () use ($datos, $id) {
            $inscripcion = Inscripcion::findOrFail($id);
            $inscripcion->update([
                'estudiante_id' => $datos['estudiante_id'],
                'gestion_id'    => $datos['gestion_id'],
                'fecha'         => $datos['fecha'],
            ]);

            // Eliminar grupos existentes
            $inscripcion->detalle()->delete();

            // Agregar nuevos grupos
            foreach ($datos['grupos'] as $grupoId) {
                DetalleInscripcion::create([
                    'inscripcion_id' => $inscripcion->id,
                    'grupo_id'       => $grupoId,
                ]);
            }

            return Inscripcion::with('gestion', 'estudiante', 'detalle')->find($inscripcion->id);
        });
    }

    public function eliminar($id)
    {
        return DB::transaction(function () use ($id) {
            $inscripcion = Inscripcion::findOrFail($id);
            $inscripcion->detalle()->delete();
            $inscripcion->delete();
            return response()->json(['message' => 'Inscripción eliminada correctamente.'], 200);
        });
    }

}