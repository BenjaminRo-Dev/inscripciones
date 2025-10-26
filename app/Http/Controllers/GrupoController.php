<?php

namespace App\Http\Controllers;

use App\Models\Estudiante;
use App\Models\Grupo;
use App\Models\GrupoEstudiante;
use App\Models\Materia;
use Illuminate\Http\Request;

class GrupoController extends Controller
{
    public function index()
    {
        $grupos = Grupo::with('horarios', 'horarios.aula')->get();
        return response()->json($grupos);
    }

    public function historialEstudiante($estudianteId)
    {
        $grupoEstudiantes = GrupoEstudiante::with('grupo.materia')
            ->where('estudiante_id', $estudianteId)
            ->get();

        $historial = $grupoEstudiantes->map(function ($ge) {
            return [
                'materia'  => $ge->grupo->materia->nombre ?? null,
                'nota'     => $ge->nota,
                'grupo_id' => $ge->grupo->id ?? null,
            ];
        })->values();

        return response()->json($historial);
    }

    public function materiasPendientes($estudianteId)
    {
        // Traer los IDs de materias ya aprobadas por el estudiante
        $materiasAprobadasIds = GrupoEstudiante::where('estudiante_id', $estudianteId)
            ->pluck('grupo_id')
            ->toArray();

        // Traer las materias cuyos grupos NO están en las aprobadas
        $materiasPendientes = Materia::whereDoesntHave('grupos', function ($query) use ($materiasAprobadasIds) {
            $query->whereIn('id', $materiasAprobadasIds);
        })->get();

        return response()->json($materiasPendientes);
    }

    public function materiasInscribibles($estudianteId)
    {
        // 1. Obtener el estudiante con su plan de estudio
        $estudiante = Estudiante::findOrFail($estudianteId);

        // 2. Obtener todas las materias aprobadas del estudiante (nota >= 51)
        $materiasAprobadas = GrupoEstudiante::where('estudiante_id', $estudianteId)
            ->where('nota', '>=', 51) // Asumiendo que 51 es la nota mínima para aprobar
            ->with('grupo.materia')
            ->get()
            ->pluck('grupo.materia.id')
            ->unique()
            ->toArray();

        // 3. Obtener todas las materias inscritas actualmente (para no duplicar)
        $materiasInscritas = GrupoEstudiante::where('estudiante_id', $estudianteId)
            ->whereHas('grupo', function ($query) {
                // Puedes filtrar por gestión actual si lo necesitas
                // $query->where('gestion_id', $gestionActualId);
            })
            ->with('grupo.materia')
            ->get()
            ->pluck('grupo.materia.id')
            ->unique()
            ->toArray();

        // 4. Obtener grupos disponibles según el plan de estudio
        $gruposDisponibles = Grupo::with(['materia.prerequisitos', 'gestion', 'horarios'])
            ->whereHas('materia', function ($query) use ($estudiante) {
                // Filtrar materias del plan de estudio del estudiante
                $query->whereHas('materiaPlan', function ($q) use ($estudiante) {
                    $q->where('plan_estudio_id', $estudiante->plan_estudio_id);
                });
            })
            // Puedes filtrar por gestión actual si lo necesitas
            // ->where('gestion_id', $gestionActualId)
            ->get()
            ->filter(function ($grupo) use ($materiasAprobadas, $materiasInscritas) {
                // No mostrar materias ya inscritas
                if (in_array($grupo->materia->id, $materiasInscritas)) {
                    return false;
                }

                // Verificar si cumple con todos los prerequisitos
                $prerequisitos = $grupo->materia->prerequisitos->pluck('id')->toArray();

                // Si no tiene prerequisitos, puede inscribirse
                if (empty($prerequisitos)) {
                    return true;
                }

                // Verificar que todas las materias prerequisito estén aprobadas
                foreach ($prerequisitos as $prereqId) {
                    if (!in_array($prereqId, $materiasAprobadas)) {
                        return false; // Falta aprobar algún prerequisito
                    }
                }

                return true; // Cumple con todos los prerequisitos
            })
            ->map(function ($grupo) {
                return [
                    'grupo_id' => $grupo->id,
                    'sigla_grupo' => $grupo->sigla,
                    'cupo' => $grupo->cupo,
                    'materia_id' => $grupo->materia->id,
                    'materia_nombre' => $grupo->materia->nombre,
                    'materia_sigla' => $grupo->materia->sigla,
                    'creditos' => $grupo->materia->creditos,
                    'gestion' => $grupo->gestion->nombre ?? null,
                    'horarios' => $grupo->horarios->map(function ($h) {
                        return [
                            'dia' => $h->dia,
                            'hora_inicio' => $h->hora_inicio,
                            'hora_fin' => $h->hora_fin,
                            'aula' => $h->aula->numero ?? null
                        ];
                    })
                ];
            })
            ->values();

        return response()->json($gruposDisponibles);
    }
}
