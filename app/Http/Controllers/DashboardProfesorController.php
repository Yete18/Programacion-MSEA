<?php

namespace App\Http\Controllers;

use App\Services\ProfilePhotoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardProfesorController extends Controller
{
    public function show(ProfilePhotoService $profilePhotoService)
    {
        if (session('rol') !== 'profesor') {
            return redirect('/login')->with('error', 'Inicia sesion como profesor para continuar');
        }

        $idUsuario = session('usuario_id');

        $profesor = DB::table('profesores as p')
            ->join('usuarios as u', 'u.id_usuario', '=', 'p.id_usuario')
            ->where('p.id_usuario', $idUsuario)
            ->select([
                'p.id_profesor',
                'u.id_usuario',
                'u.nombres',
                'u.apellido_paterno',
                'u.apellido_materno',
                'u.correo',
                'u.foto',
            ])
            ->first();

        if (! $profesor) {
            session()->invalidate();

            return redirect('/login')->with('error', 'No encontramos el perfil de profesor para este usuario');
        }

        $driver = DB::connection()->getDriverName();
        $instrumentosSql = $driver === 'pgsql'
            ? "coalesce(string_agg(distinct i.nombre, ', '), 'Sin instrumento') as instrumento"
            : "coalesce(group_concat(distinct i.nombre), 'Sin instrumento') as instrumento";
        $completadasSql = $driver === 'pgsql'
            ? "count(pr.id_progreso) filter (where pr.estado = 'completado') as completadas"
            : "sum(case when pr.estado = 'completado' then 1 else 0 end) as completadas";

        $alumnos = DB::table('estudiantes as e')
            ->join('usuarios as u', 'u.id_usuario', '=', 'e.id_usuario')
            ->leftJoin('usuario_instrumento as ui', 'ui.id_usuario', '=', 'u.id_usuario')
            ->leftJoin('instrumentos as i', 'i.id_instrumento', '=', 'ui.id_instrumento')
            ->leftJoin('progreso as pr', 'pr.id_estudiante', '=', 'e.id_estudiante')
            ->where('e.id_profesor', $profesor->id_profesor)
            ->select([
                'e.id_estudiante',
                'u.nombres',
                'u.apellido_paterno',
                'u.apellido_materno',
                DB::raw($instrumentosSql),
                DB::raw('coalesce(sum(pr.puntaje), 0) as puntos'),
                DB::raw($completadasSql),
            ])
            ->groupBy('e.id_estudiante', 'u.nombres', 'u.apellido_paterno', 'u.apellido_materno')
            ->orderBy('u.apellido_paterno')
            ->get()
            ->map(function ($alumno) {
                $puntos = (int) round((float) $alumno->puntos);
                $xpMax = 500;

                return [
                    'id' => $alumno->id_estudiante,
                    'nombre' => trim($alumno->nombres.' '.$alumno->apellido_paterno.' '.$alumno->apellido_materno),
                    'avatar' => '🎓',
                    'instrumento' => $alumno->instrumento,
                    'nivel' => 'Nivel '.max(1, intdiv($puntos, 500) + 1),
                    'xp' => $puntos % $xpMax,
                    'xpMax' => $xpMax,
                    'puntos' => $puntos,
                    'racha' => 0,
                    'completadas' => (int) $alumno->completadas,
                ];
            })
            ->values()
            ->all();

        $totalAlumnos = count($alumnos);
        $tareasCompletadas = array_sum(array_column($alumnos, 'completadas'));
        $nombreCompleto = trim($profesor->nombres.' '.$profesor->apellido_paterno.' '.$profesor->apellido_materno);
        $especialidad = DB::table('usuario_instrumento as ui')
            ->join('instrumentos as i', 'i.id_instrumento', '=', 'ui.id_instrumento')
            ->where('ui.id_usuario', $profesor->id_usuario)
            ->orderBy('i.nombre')
            ->pluck('i.nombre')
            ->implode(', ');

        if (! $especialidad) {
            $especialidad = collect($alumnos)->pluck('instrumento')->filter()->first() ?: 'Sin especialidad';
        }

        $profesorData = [
            'idProfesor' => $profesor->id_profesor,
            'nombre' => $nombreCompleto ?: $profesor->nombres,
            'primerNombre' => $profesor->nombres,
            'usuario' => 'prof_'.$profesor->id_profesor,
            'email' => $profesor->correo,
            'especialidad' => $especialidad,
            'foto' => $profilePhotoService->publicValue($profesor->foto),
            'totalAlumnos' => $totalAlumnos,
            'tareasRevision' => 0,
            'tareasCompletadas' => $tareasCompletadas,
            'clasesHoy' => 0,
            'miembroDesde' => 'Sin fecha',
        ];

        return view('dashboard-profesor', [
            'profesorData' => $profesorData,
            'alumnosData' => $alumnos,
            'tareasData' => $this->tareasData((int) $profesor->id_profesor),
        ]);
    }

    public function storeTask(Request $request)
    {
        if (session('rol') !== 'profesor') {
            return response()->json(['message' => 'Inicia sesion como profesor para continuar'], 401);
        }

        $validated = $request->validate([
            'titulo' => ['required', 'string', 'max:150'],
            'descripcion' => ['nullable', 'string', 'max:1000'],
            'id_estudiante' => ['required', 'integer', 'exists:estudiantes,id_estudiante'],
            'fecha_limite' => ['nullable', 'date'],
            'xp_recompensa' => ['nullable', 'integer', 'min:5', 'max:200'],
            'tipo' => ['nullable', 'string', 'max:50'],
            'archivo' => ['nullable', 'string', 'max:500'],
        ]);

        $profesor = DB::table('profesores')->where('id_usuario', session('usuario_id'))->first();

        if (! $profesor) {
            return response()->json(['message' => 'No encontramos el perfil de profesor'], 404);
        }

        $studentBelongsToTeacher = DB::table('estudiantes')
            ->where('id_estudiante', $validated['id_estudiante'])
            ->where('id_profesor', $profesor->id_profesor)
            ->exists();

        if (! $studentBelongsToTeacher) {
            return response()->json(['message' => 'El estudiante no esta asignado a este profesor'], 422);
        }

        $task = DB::transaction(function () use ($validated, $profesor) {
            $idTipo = DB::table('tipos_ejercicio')->where('nombre', $validated['tipo'] ?? 'Repertorio')->value('id_tipo')
                ?: DB::table('tipos_ejercicio')->insertGetId(['nombre' => $validated['tipo'] ?? 'Repertorio'], 'id_tipo');

            $idEjercicio = DB::table('ejercicios')->insertGetId([
                'id_tipo' => $idTipo,
                'titulo' => $validated['titulo'],
                'descripcion' => $validated['descripcion'] ?? null,
                'archivo' => $validated['archivo'] ?? null,
                'creado_por' => $profesor->id_profesor,
                'dificultad' => 'basico',
                'xp' => $validated['xp_recompensa'] ?? 30,
            ], 'id_ejercicio');

            $idTarea = DB::table('tareas')->insertGetId([
                'titulo' => $validated['titulo'],
                'descripcion' => $validated['descripcion'] ?? null,
                'fecha_creacion' => now(),
                'id_profesor' => $profesor->id_profesor,
                'id_estudiante' => $validated['id_estudiante'],
                'fecha_limite' => $validated['fecha_limite'] ?? null,
                'xp_recompensa' => $validated['xp_recompensa'] ?? 30,
                'estado' => 'pendiente',
            ], 'id_tarea');

            DB::table('tarea_ejercicio')->insert([
                'id_tarea' => $idTarea,
                'id_ejercicio' => $idEjercicio,
            ]);

            if (Schema::hasTable('notificaciones')) {
                $idUsuario = DB::table('estudiantes')->where('id_estudiante', $validated['id_estudiante'])->value('id_usuario');
                DB::table('notificaciones')->insert([
                    'id_usuario' => $idUsuario,
                    'titulo' => 'Nueva tarea asignada',
                    'mensaje' => $validated['titulo'],
                    'tipo' => 'tarea',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            return DB::table('tareas')->where('id_tarea', $idTarea)->first();
        });

        return response()->json([
            'message' => 'Tarea creada correctamente',
            'tarea' => $task,
        ], 201);
    }

    private function tareasData(int $idProfesor): array
    {
        $instrumentosSql = DB::connection()->getDriverName() === 'pgsql'
            ? "coalesce(string_agg(distinct i.nombre, ', '), 'Sin instrumento') as instrumento"
            : "coalesce(group_concat(distinct i.nombre), 'Sin instrumento') as instrumento";

        $query = DB::table('tareas as t')
            ->join('estudiantes as e', 'e.id_estudiante', '=', 't.id_estudiante')
            ->join('usuarios as u', 'u.id_usuario', '=', 'e.id_usuario')
            ->leftJoin('usuario_instrumento as ui', 'ui.id_usuario', '=', 'u.id_usuario')
            ->leftJoin('instrumentos as i', 'i.id_instrumento', '=', 'ui.id_instrumento')
            ->leftJoin('entregas_tareas as et', 'et.id_tarea', '=', 't.id_tarea')
            ->where('t.id_profesor', $idProfesor)
            ->select([
                't.id_tarea',
                't.titulo',
                't.fecha_limite',
                'u.nombres',
                'u.apellido_paterno',
                'et.id_entrega',
                'et.comentario_estudiante',
                'et.archivo as archivo_entrega',
                DB::raw($instrumentosSql),
                DB::raw("coalesce(et.estado, t.estado, 'pendiente') as estado_tarea"),
            ])
            ->groupBy('t.id_tarea', 't.titulo', 't.fecha_limite', 'u.nombres', 'u.apellido_paterno', 'et.id_entrega', 'et.comentario_estudiante', 'et.archivo', 'et.estado', 't.estado')
            ->orderByDesc('t.fecha_creacion');

        return $query->get()->map(fn ($tarea) => [
            'id' => $tarea->id_tarea,
            'titulo' => $tarea->titulo,
            'alumno' => trim($tarea->nombres.' '.$tarea->apellido_paterno),
            'instrumento' => $tarea->instrumento,
            'limite' => $tarea->fecha_limite,
            'estado' => $tarea->estado_tarea ?: 'pendiente',
            'id_entrega' => $tarea->id_entrega,
            'comentario_estudiante' => $tarea->comentario_estudiante,
            'archivo_entrega' => $tarea->archivo_entrega,
        ])->all();
    }

    public function gradeTask(Request $request, int $idEntrega, \App\Services\GamificationService $gamificationService)
    {
        if (session('rol') !== 'profesor') {
            return response()->json(['message' => 'Inicia sesion como profesor para continuar'], 401);
        }

        $profesor = DB::table('profesores')->where('id_usuario', session('usuario_id'))->first();

        if (! $profesor) {
            return response()->json(['message' => 'No encontramos el perfil de profesor'], 404);
        }

        $entrega = DB::table('entregas_tareas as et')
            ->join('tareas as t', 't.id_tarea', '=', 'et.id_tarea')
            ->where('et.id_entrega', $idEntrega)
            ->where('t.id_profesor', $profesor->id_profesor)
            ->select('et.*', 't.titulo', 't.xp_recompensa')
            ->first();

        if (! $entrega) {
            return response()->json(['message' => 'No se encontro la entrega o no tienes permiso para calificarla'], 404);
        }

        $validated = $request->validate([
            'calificacion' => ['required', 'numeric', 'min:0', 'max:100'],
            'comentario_profesor' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($idEntrega, $entrega, $validated, $gamificationService) {
            DB::table('entregas_tareas')
                ->where('id_entrega', $idEntrega)
                ->update([
                    'calificacion' => $validated['calificacion'],
                    'comentario_profesor' => $validated['comentario_profesor'] ?? null,
                    'estado' => 'calificada',
                    'calificado_at' => now(),
                    'updated_at' => now(),
                ]);

            DB::table('tareas')
                ->where('id_tarea', $entrega->id_tarea)
                ->update(['estado' => 'calificada']);

            // Otorgar XP al estudiante
            $gamificationService->awardXp(
                (int) $entrega->id_estudiante,
                (int) $entrega->xp_recompensa,
                "Tarea calificada: {$entrega->titulo}"
            );

            // Notificar al estudiante
            if (Schema::hasTable('notificaciones')) {
                $idUsuarioEstudiante = DB::table('estudiantes')->where('id_estudiante', $entrega->id_estudiante)->value('id_usuario');
                if ($idUsuarioEstudiante) {
                    $califInt = (int) $validated['calificacion'];
                    DB::table('notificaciones')->insert([
                        'id_usuario' => $idUsuarioEstudiante,
                        'titulo' => 'Tarea calificada 🎉',
                        'mensaje' => "Tu tarea '{$entrega->titulo}' fue calificada con {$califInt}/100.",
                        'tipo' => 'tarea',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        });

        return response()->json([
            'message' => 'Tarea calificada correctamente',
        ]);
    }

    public function studentProgress(int $idEstudiante)
    {
        if (session('rol') !== 'profesor') {
            return response()->json(['message' => 'Inicia sesion como profesor para continuar'], 401);
        }

        $profesor = DB::table('profesores')->where('id_usuario', session('usuario_id'))->first();

        if (! $profesor) {
            return response()->json(['message' => 'No encontramos el perfil de profesor'], 404);
        }

        $estudiante = DB::table('estudiantes as e')
            ->join('usuarios as u', 'u.id_usuario', '=', 'e.id_usuario')
            ->where('e.id_estudiante', $idEstudiante)
            ->where('e.id_profesor', $profesor->id_profesor)
            ->select('e.*', 'u.nombres', 'u.apellido_paterno', 'u.apellido_materno')
            ->first();

        if (! $estudiante) {
            return response()->json(['message' => 'Estudiante no encontrado o no asignado a ti'], 404);
        }

        // 1. Estadísticas de práctica
        $duracionTotal = DB::table('practicas_autonomas')
            ->where('id_estudiante', $idEstudiante)
            ->sum('duracion_segundos') ?? 0;

        $ejerciciosCompletados = DB::table('progreso')
            ->where('id_estudiante', $idEstudiante)
            ->where('estado', 'completado')
            ->count();

        $afinacionesHechas = DB::table('sesiones_afinacion')
            ->where('id_estudiante', $idEstudiante)
            ->count();

        $precisionRitmoPromedio = DB::table('sesiones_ritmo')
            ->where('id_estudiante', $idEstudiante)
            ->avg('precision') ?? 0;

        // 2. Historial de práctica
        $practicas = DB::table('practicas_autonomas')
            ->where('id_estudiante', $idEstudiante)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get()
            ->map(fn ($p) => [
                'tipo' => 'Autónoma: ' . $p->tipo,
                'fecha' => date('d/m/Y H:i', strtotime($p->created_at)),
                'detalle' => round($p->duracion_segundos / 60, 1) . ' min | +' . $p->xp_ganado . ' XP',
            ]);

        $afinaciones = DB::table('sesiones_afinacion')
            ->where('id_estudiante', $idEstudiante)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get()
            ->map(fn ($a) => [
                'tipo' => 'Afinador',
                'fecha' => date('d/m/Y H:i', strtotime($a->created_at)),
                'detalle' => 'Nota: ' . ($a->nota_detectada ?? '-') . ' (' . ($a->frecuencia ?? '-') . ' Hz) | Desv: ' . round($a->desviacion_cents ?? 0, 1) . ' cents',
            ]);

        $ritmos = DB::table('sesiones_ritmo')
            ->where('id_estudiante', $idEstudiante)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get()
            ->map(fn ($r) => [
                'tipo' => 'Rítmico',
                'fecha' => date('d/m/Y H:i', strtotime($r->created_at)),
                'detalle' => 'Precisión: ' . round($r->precision ?? 0, 1) . '% | BPM: ' . $r->bpm . ' | +' . $r->xp_ganado . ' XP',
            ]);

        $historial = collect($practicas)->concat($afinaciones)->concat($ritmos)
            ->sortByDesc('fecha')
            ->take(10)
            ->values()
            ->all();

        return response()->json([
            'estudiante' => [
                'nombre' => trim($estudiante->nombres . ' ' . $estudiante->apellido_paterno),
                'totalMinutos' => round($duracionTotal / 60, 1),
                'ejerciciosHechos' => $ejerciciosCompletados,
                'afinacionesHechas' => $afinacionesHechas,
                'precisionRitmo' => round($precisionRitmoPromedio, 1),
            ],
            'historial' => $historial,
        ]);
    }
}
