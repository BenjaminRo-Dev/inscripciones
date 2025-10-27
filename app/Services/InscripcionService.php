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
    public function __construct(
        protected Repositories\InscripcionRepository $repository,
        protected Validators\CupoValidator $cupoValidator,
        protected Validators\HorarioValidator $horarioValidator,
    ) {
    }

    public function mostrar($datos)
    {
        return Inscripcion::with('detalle')->findOrFail($datos['id']);
    }

    public function mostrarTodos()
    {
        return Inscripcion::all();
    }

    //Todos o nada
    public function guardar(array $datos)
    {
        try {
            DB::beginTransaction();

            // if (!$this->cupoValidator->validarCupos($datos['grupos'])) {
            //     throw new CupoCeroException("Uno o más grupos no tienen cupos disponibles.");
            // }
            
            $this->cupoValidator->validarCupos($datos['grupos']);

            $choques = $this->horarioValidator->validarChoqueHorarios($datos['grupos']);

            if (!empty($choques)) {
                throw new ChoqueHorarioException(
                    "Existen choques de horario entre los grupos: " .
                        implode(', ', array_map(fn($p) => "({$p[0]}, {$p[1]})", $choques))
                );
            }

            $inscripcion = $this->repository->inscribir($datos);

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

    //Realizar la inscripcion de los grupos que si tienen cupos e ingnorar los que no lo tienen
    public function guardarParcial(array $datos)
    {
        try {
            DB::beginTransaction();

            $resultado = $this->repository->filtrarGruposConCupos($datos['grupos']);
            $gruposValidos = $resultado['validos'];
            $gruposSinCupo = $resultado['sin_cupo'];

            if (empty($gruposValidos)) {
                throw new CupoCeroException("Ninguno de los grupos tiene cupos disponibles.");
            }

            $datos['grupos'] = $gruposValidos;
            $inscripcion = $this->repository->inscribir($datos);

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
