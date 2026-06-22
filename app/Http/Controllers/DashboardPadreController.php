<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class DashboardPadreController extends Controller
{
    public function show()
    {
        $idUsuario = (int) session('usuario_id');

        $padre = DB::table('padres')->where('id_usuario', $idUsuario)->first();
        $estudiantes = collect();

        if ($padre) {
            $estudiantes = DB::table('estudiante_padre as ep')
                ->join('estudiantes as e', 'e.id_estudiante', '=', 'ep.id_estudiante')
                ->join('usuarios as u', 'u.id_usuario', '=', 'e.id_usuario')
                ->leftJoin('gamificacion_perfiles as g', 'g.id_estudiante', '=', 'e.id_estudiante')
                ->leftJoin('ranking_estudiantes as r', 'r.id_estudiante', '=', 'e.id_estudiante')
                ->where('ep.id_padre', $padre->id_padre)
                ->select([
                    'e.id_estudiante',
                    'e.fecha_ingreso',
                    'u.nombres',
                    'u.apellido_paterno',
                    'u.apellido_materno',
                    'u.foto',
                    DB::raw('coalesce(g.xp_total, 0) as xp_total'),
                    DB::raw('coalesce(g.nivel, 1) as nivel'),
                    DB::raw('coalesce(g.racha_actual, 0) as racha_actual'),
                    DB::raw('coalesce(r.posicion, 0) as posicion'),
                ])
                ->get()
                ->map(function ($alumno) {
                    $idEstudiante = $alumno->id_estudiante;

                    // 1. Instrumentos
                    $instrumentos = DB::table('usuario_instrumento as ui')
                        ->join('instrumentos as i', 'i.id_instrumento', '=', 'ui.id_instrumento')
                        ->join('estudiantes as est_i', 'est_i.id_usuario', '=', 'ui.id_usuario')
                        ->where('est_i.id_estudiante', $idEstudiante)
                        ->pluck('i.nombre')
                        ->implode(', ') ?: 'Sin instrumento';

                    // 2. Tiempo de práctica semanal (últimos 7 días)
                    $practicasSemanales = DB::table('practicas_autonomas')
                        ->where('id_estudiante', $idEstudiante)
                        ->where('created_at', '>=', now()->subDays(7))
                        ->selectRaw('DATE(created_at) as fecha, SUM(duracion_segundos) as segundos')
                        ->groupBy('fecha')
                        ->pluck('segundos', 'fecha')
                        ->all();

                    // Rellenar los últimos 7 días con 0 si no hay práctica
                    $semanaData = [];
                    $diasSemana = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
                    for ($i = 6; $i >= 0; $i--) {
                        $date = now()->subDays($i)->toDateString();
                        $segundos = $practicasSemanales[$date] ?? 0;
                        $nombreDia = $diasSemana[now()->subDays($i)->dayOfWeek];
                        $semanaData[] = [
                            'dia' => $nombreDia,
                            'minutos' => round($segundos / 60, 1),
                        ];
                    }

                    // 3. Tareas
                    $tareas = DB::table('tareas as t')
                        ->leftJoin('entregas_tareas as et', function ($join) use ($idEstudiante) {
                            $join->on('et.id_tarea', '=', 't.id_tarea')->where('et.id_estudiante', '=', $idEstudiante);
                        })
                        ->where('t.id_estudiante', $idEstudiante)
                        ->select([
                            't.titulo',
                            't.fecha_limite',
                            't.xp_recompensa',
                            DB::raw("coalesce(et.estado, t.estado, 'pendiente') as estado_tarea"),
                            'et.calificacion',
                            'et.comentario_profesor',
                            'et.calificado_at',
                        ])
                        ->get()
                        ->map(fn ($t) => [
                            'titulo' => $t->titulo,
                            'limite' => $t->fecha_limite ? date('d/m/Y', strtotime($t->fecha_limite)) : 'Sin fecha',
                            'xp' => $t->xp_recompensa,
                            'estado' => $t->estado_tarea,
                            'calificacion' => $t->calificacion ? (int) $t->calificacion : null,
                            'comentario_profesor' => $t->comentario_profesor,
                        ]);

                    // 4. Logros del niño
                    $logros = DB::table('estudiante_logro as el')
                        ->join('logros as l', 'l.id_logro', '=', 'el.id_logro')
                        ->where('el.id_estudiante', $idEstudiante)
                        ->select('l.nombre', 'l.descripcion', 'el.desbloqueado_at')
                        ->get()
                        ->map(fn ($l) => [
                            'nombre' => $l->nombre,
                            'desc' => $l->descripcion,
                            'desbloqueado_at' => date('d/m/Y', strtotime($l->desbloqueado_at)),
                        ]);

                    // 5. Total minutos acumulados históricos
                    $minutosTotales = DB::table('practicas_autonomas')
                        ->where('id_estudiante', $idEstudiante)
                        ->sum('duracion_segundos') ?? 0;

                    return [
                        'id' => $alumno->id_estudiante,
                        'nombre' => trim($alumno->nombres.' '.$alumno->apellido_paterno.' '.$alumno->apellido_materno),
                        'foto' => $alumno->foto,
                        'nivel' => $alumno->nivel,
                        'xp_total' => $alumno->xp_total,
                        'racha' => $alumno->racha_actual,
                        'posicion' => $alumno->posicion,
                        'instrumentos' => $instrumentos,
                        'semana_practica' => $semanaData,
                        'tareas' => $tareas,
                        'logros' => $logros,
                        'total_minutos' => round($minutosTotales / 60, 1),
                    ];
                });
        }

        return view('dashboard-padre', [
            'estudiantes' => $estudiantes,
            'padre' => DB::table('usuarios')->where('id_usuario', $idUsuario)->first(),
        ]);
    }
}
