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
    public function mostrar($datos)
    {
        // return $datos;
        return Inscripcion::with('detalle')->findOrFail($datos['id']);
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

            $inscripcion = $this->inscribir($datos);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Inscripción guardada correctamente.',
                'data' => Inscripcion::with('gestion', 'estudiante', 'detalle')->find($inscripcion->id)
            ];
        } catch (\Throwable $e) {
            return $this->manejoError($datos, $e);
        }
    }

    private function validarCupos(array $gruposIds): bool
    {
        foreach ($gruposIds as $grupoId) {
            // $grupo = Grupo::findOrFail($grupoId);
            $grupo = Grupo::where('id', $grupoId)->lockForUpdate()->firstOrFail();
            if ($grupo->cupo <= 0) {
                return false;
            }
        }
        return true;
    }

    //Realizar la inscripcion de los grupos que si tienen cupos e ingnorar los que no lo tienen
    public function guardarParcial(array $datos)
    {
        try {
            DB::beginTransaction();

            $resultado = $this->filtrarGruposConCupos($datos['grupos']);
            $gruposValidos = $resultado['validos'];
            $gruposSinCupo = $resultado['sin_cupo'];

            if (empty($gruposValidos)) {
                throw new CupoCeroException("Ninguno de los grupos tiene cupos disponibles.");
            }

            $datos['grupos'] = $gruposValidos;
            $inscripcion = $this->inscribir($datos);

            DB::commit();

            foreach ($gruposSinCupo as $grupoId) {
                log()->warning("Grupo sin cupo (no inscrito) en transacción {$datos['uuid']}: Grupo ID {$grupoId}");
            }

            return [
                'success' => true,
                'message' => 'Inscripción parcial completada.',
                'data' => [
                    'inscripcion' => Inscripcion::with('gestion', 'estudiante', 'detalle')->find($inscripcion->id),
                    'grupos_inscritos' => $gruposValidos,
                    'grupos_sin_cupo' => $gruposSinCupo,
                ],
            ];
        } catch (\Throwable $e) {
            return $this->manejoError($datos, $e);
        }
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

    private function inscribir($datos): Inscripcion
    {
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
        return $inscripcion;
    }

    private function filtrarGruposConCupos(array $gruposIds): array
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

    private function manejoError(array $datos, \Throwable $e)
    {
        if (DB::transactionLevel() > 0) {
            DB::rollBack();
        }

        log()->error("Error al guardar inscripción en transacción: {$datos['uuid']} - {$e->getMessage()}");

        return [
            'success' => false,
            'message' => 'Error al guardar la inscripción: ' . $e->getMessage(),
            'data' => null,
            'code' => $e instanceof CupoCeroException ? 409 : 422,
        ];
    }


    public function actualizar(array $datos, $id)
    {
        return $datos;
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

    public function eliminar($datos)
    {
        // return $datos;
        $id = $datos['id'];
        return DB::transaction(function () use ($id) {
            $inscripcion = Inscripcion::findOrFail($id);
            $inscripcion->detalle()->delete();
            $inscripcion->delete();
            return [
                'success' => true,
                'message' => 'Inscripción eliminada correctamente.',
                'data' => 'id eliminado: ' . $id,
            ];
        });
    }
}
