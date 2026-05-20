<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class DashboardProfesorController extends Controller
{
    public function show()
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
                DB::raw("coalesce(string_agg(distinct i.nombre, ', '), 'Sin instrumento') as instrumento"),
                DB::raw('coalesce(sum(pr.puntaje), 0) as puntos'),
                DB::raw("count(pr.id_progreso) filter (where pr.estado = 'completado') as completadas"),
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
            'foto' => $profesor->foto,
            'totalAlumnos' => $totalAlumnos,
            'tareasRevision' => 0,
            'tareasCompletadas' => $tareasCompletadas,
            'clasesHoy' => 0,
            'miembroDesde' => 'Sin fecha',
        ];

        return view('dashboard-profesor', [
            'profesorData' => $profesorData,
            'alumnosData' => $alumnos,
            'tareasData' => [],
        ]);
    }
}
