<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class DashboardEstudianteController extends Controller
{
    public function show()
    {
        if (session('rol') !== 'estudiante') {
            return redirect('/login')->with('error', 'Inicia sesion como estudiante para continuar');
        }

        $idUsuario = session('usuario_id');

        $estudiante = DB::table('estudiantes as e')
            ->join('usuarios as u', 'u.id_usuario', '=', 'e.id_usuario')
            ->leftJoin('secciones as s', 's.id_seccion', '=', 'e.id_seccion')
            ->leftJoin('profesores as p', 'p.id_profesor', '=', 'e.id_profesor')
            ->leftJoin('usuarios as up', 'up.id_usuario', '=', 'p.id_usuario')
            ->where('e.id_usuario', $idUsuario)
            ->select([
                'e.id_estudiante',
                'e.fecha_ingreso',
                'u.id_usuario',
                'u.correo',
                'u.nombres',
                'u.apellido_paterno',
                'u.apellido_materno',
                'u.ci',
                'u.celular',
                'u.direccion',
                'u.fecha_nacimiento',
                'u.foto',
                's.nombre as seccion',
                DB::raw("concat_ws(' ', up.nombres, up.apellido_paterno) as profesor_nombre"),
            ])
            ->first();

        if (! $estudiante) {
            session()->invalidate();

            return redirect('/login')->with('error', 'No encontramos el perfil de estudiante para este usuario');
        }

        $instrumentos = DB::table('usuario_instrumento as ui')
            ->join('instrumentos as i', 'i.id_instrumento', '=', 'ui.id_instrumento')
            ->where('ui.id_usuario', $idUsuario)
            ->orderBy('i.nombre')
            ->pluck('i.nombre')
            ->all();

        $stats = DB::table('progreso')
            ->where('id_estudiante', $estudiante->id_estudiante)
            ->selectRaw('coalesce(sum(puntaje), 0) as puntos')
            ->selectRaw("count(*) filter (where estado = 'completado') as ejercicios_hechos")
            ->first();

        $puntos = (int) round((float) ($stats->puntos ?? 0));
        $nivel = max(1, intdiv($puntos, 500) + 1);
        $xp = $puntos % 500;
        $xpMax = 500;

        $ranking = DB::table('ranking_estudiantes')
            ->where('id_estudiante', $estudiante->id_estudiante)
            ->first();

        $rankingPos = $ranking ? (int) $ranking->posicion : 0;

        $logrosDesbloqueados = DB::table('estudiante_recompensa')
            ->where('id_estudiante', $estudiante->id_estudiante)
            ->count();

        $dashboardData = [
            'idEstudiante' => $estudiante->id_estudiante,
            'nombre' => $estudiante->nombres,
            'apellidoPaterno' => $estudiante->apellido_paterno,
            'apellidoMaterno' => $estudiante->apellido_materno,
            'apellido' => trim(($estudiante->apellido_paterno ?? '').' '.($estudiante->apellido_materno ?? '')),
            'nombreCompleto' => trim($estudiante->nombres.' '.$estudiante->apellido_paterno.' '.$estudiante->apellido_materno),
            'email' => $estudiante->correo,
            'ci' => $estudiante->ci,
            'celular' => $estudiante->celular,
            'direccion' => $estudiante->direccion,
            'fechaNacimiento' => $estudiante->fecha_nacimiento,
            'foto' => $estudiante->foto,
            'instrumento' => count($instrumentos) ? implode(', ', $instrumentos) : 'Sin instrumento',
            'nivel' => $nivel,
            'nivelTexto' => 'Nivel '.$nivel,
            'xp' => $xp,
            'xpMax' => $xpMax,
            'puntos' => $puntos,
            'racha' => 0,
            'ejerciciosHechos' => (int) ($stats->ejercicios_hechos ?? 0),
            'rankingPos' => $rankingPos,
            'seccion' => $estudiante->seccion ?: 'Sin seccion',
            'sede' => $estudiante->seccion ? 'Seccion '.$estudiante->seccion : 'Sin seccion',
            'profesor' => $estudiante->profesor_nombre ?: 'Sin profesor asignado',
            'miembroDesde' => $estudiante->fecha_ingreso
                ? date('d/m/Y', strtotime($estudiante->fecha_ingreso))
                : 'Sin fecha',
            'logrosDesbloqueados' => $logrosDesbloqueados,
        ];

        $rankingData = DB::table('ranking_estudiantes as r')
            ->join('estudiantes as e', 'e.id_estudiante', '=', 'r.id_estudiante')
            ->leftJoin('usuario_instrumento as ui', 'ui.id_usuario', '=', 'e.id_usuario')
            ->leftJoin('instrumentos as i', 'i.id_instrumento', '=', 'ui.id_instrumento')
            ->select([
                'r.id_estudiante',
                'r.nombres',
                'r.apellido_paterno',
                'r.puntaje_total',
                'r.posicion',
                DB::raw("coalesce(string_agg(i.nombre, ', ' order by i.nombre), 'Sin instrumento') as instrumentos"),
            ])
            ->groupBy('r.id_estudiante', 'r.nombres', 'r.apellido_paterno', 'r.puntaje_total', 'r.posicion')
            ->orderBy('r.posicion')
            ->limit(10)
            ->get()
            ->map(fn ($item) => [
                'nombre' => trim($item->nombres.' '.$item->apellido_paterno),
                'puntos' => (int) round((float) $item->puntaje_total),
                'avatar' => (int) $item->posicion <= 3 ? '🏆' : '🎓',
                'instrumento' => $item->instrumentos,
                'esYo' => (int) $item->id_estudiante === (int) $estudiante->id_estudiante,
            ])
            ->all();

        return view('dashboard-estudiante', [
            'dashboardData' => $dashboardData,
            'rankingData' => $rankingData,
        ]);
    }

    public function updateProfile(Request $request)
    {
        if (session('rol') !== 'estudiante') {
            return response()->json([
                'message' => 'Inicia sesion como estudiante para continuar',
            ], 401);
        }

        $idUsuario = (int) session('usuario_id');

        $estudianteExiste = DB::table('estudiantes')
            ->where('id_usuario', $idUsuario)
            ->exists();

        if (! $estudianteExiste) {
            return response()->json([
                'message' => 'No encontramos el perfil de estudiante para este usuario',
            ], 404);
        }

        $validated = $request->validate([
            'nombres' => ['required', 'string', 'max:100'],
            'apellido_paterno' => ['nullable', 'string', 'max:100'],
            'apellido_materno' => ['nullable', 'string', 'max:100'],
            'correo' => [
                'required',
                'email',
                'max:100',
                Rule::unique('usuarios', 'correo')->ignore($idUsuario, 'id_usuario'),
            ],
            'ci' => ['nullable', 'string', 'max:20'],
            'celular' => ['nullable', 'string', 'max:20'],
            'direccion' => ['nullable', 'string', 'max:500'],
            'fecha_nacimiento' => ['nullable', 'date'],
            'foto' => ['nullable', 'string', 'max:3000000'],
        ]);

        if (! empty($validated['foto']) && ! preg_match('/^data:image\/(png|jpe?g|webp);base64,/', $validated['foto'])) {
            throw ValidationException::withMessages([
                'foto' => 'La foto debe ser una imagen valida.',
            ]);
        }

        $payload = [
            'nombres' => $validated['nombres'],
            'apellido_paterno' => $validated['apellido_paterno'] ?? null,
            'apellido_materno' => $validated['apellido_materno'] ?? null,
            'correo' => $validated['correo'],
            'ci' => $validated['ci'] ?? null,
            'celular' => $validated['celular'] ?? null,
            'direccion' => $validated['direccion'] ?? null,
            'fecha_nacimiento' => $validated['fecha_nacimiento'] ?? null,
        ];

        if (array_key_exists('foto', $validated)) {
            $payload['foto'] = $validated['foto'];
        }

        DB::table('usuarios')
            ->where('id_usuario', $idUsuario)
            ->update($payload);

        session([
            'nombre' => $payload['nombres'],
            'apellido_paterno' => $payload['apellido_paterno'],
            'correo' => $payload['correo'],
        ]);

        return response()->json([
            'message' => 'Perfil actualizado correctamente',
            'estudiante' => [
                'nombre' => $payload['nombres'],
                'apellidoPaterno' => $payload['apellido_paterno'],
                'apellidoMaterno' => $payload['apellido_materno'],
                'apellido' => trim(($payload['apellido_paterno'] ?? '').' '.($payload['apellido_materno'] ?? '')),
                'nombreCompleto' => trim($payload['nombres'].' '.($payload['apellido_paterno'] ?? '').' '.($payload['apellido_materno'] ?? '')),
                'email' => $payload['correo'],
                'ci' => $payload['ci'],
                'celular' => $payload['celular'],
                'direccion' => $payload['direccion'],
                'fechaNacimiento' => $payload['fecha_nacimiento'],
                'foto' => $payload['foto'] ?? null,
            ],
        ]);
    }
}
