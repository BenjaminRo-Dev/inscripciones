<?php

namespace App\Services;

use App\Exceptions\ChoqueHorarioException;
use App\Models\DetalleInscripcion;
use App\Models\Grupo;
use App\Models\Inscripcion;
use App\Exceptions\CupoCeroException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

use function Illuminate\Log\log;

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
        try {
            DB::beginTransaction();

            if (!$this->validarCupos($datos['grupos'])) {
                throw new CupoCeroException("Uno o más grupos no tienen cupos disponibles.");
            }

            $choques = $this->validarChoqueHorarios($datos['grupos']);

            if (!empty($choques)) {
                throw new ChoqueHorarioException(
                    "Existen choques de horario entre los grupos: " .
                        implode(', ', array_map(fn($p) => "({$p[0]}, {$p[1]})", $choques))
                );
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

            DB::commit();

            return [
                'success' => true,
                'message' => 'Inscripción guardada correctamente.',
                'data' => Inscripcion::with('gestion', 'estudiante', 'detalle')->find($inscripcion->id)
            ];
        } catch (\Throwable $e) {
            DB::rollBack();
            log()->error("Error al guardar la inscripción en la transaccion:". $datos['uuid'] ." -  " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error al guardar la inscripción: ' . $e->getMessage(),
                'data' => null,
                'code' => $e instanceof ChoqueHorarioException ? 409 : 422
            ];
        }
    }

    private function validarCupos(array $gruposIds): bool
    {
        foreach ($gruposIds as $grupoId) {
            $grupo = Grupo::findOrFail($grupoId);
            if ($grupo->cupo <= 0) {
                return false;
            }
        }
        return true;
    }

    private function validarChoqueHorarios(array $gruposIds): array
    {
        $gruposHorarios = [];

        foreach ($gruposIds as $grupoId) {
            $response = Http::get("http://grupos-service:3001/api/horario/grupo/{$grupoId}");
            $gruposHorarios[$grupoId] = $response->json();
        }

        $gruposEnChoque = [];

        foreach ($gruposHorarios as $grupoIdA => $horariosA) {
            foreach ($gruposHorarios as $grupoIdB => $horariosB) {
                if ($grupoIdA >= $grupoIdB) continue;

                foreach ($horariosA as $hA) {
                    foreach ($horariosB as $hB) {
                        if ($hA['dia'] === $hB['dia']) {
                            $inicioA = strtotime($hA['horaInicio']);
                            $finA    = strtotime($hA['horaFin']);
                            $inicioB = strtotime($hB['horaInicio']);
                            $finB    = strtotime($hB['horaFin']);

                            if ($inicioA < $finB && $inicioB < $finA) {
                                $gruposEnChoque[] = [$grupoIdA, $grupoIdB];
                                break 3;
                            }
                        }
                    }
                }
            }
        }

        return $gruposEnChoque;
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
