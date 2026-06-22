<?php

namespace App\Services;

use App\Models\Estudiante;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class StudentDashboardService
{
    public function __construct(private readonly ProfilePhotoService $profilePhotoService)
    {
    }

    public function dashboardPayload(int $idUsuario): ?array
    {
        $estudiante = $this->estudianteBase($idUsuario);

        if (! $estudiante) {
            return null;
        }

        $usuarioInstrumentos = Usuario::query()
            ->with([
                'instrumentos' => fn ($query) => $query
                    ->select('instrumentos.id_instrumento', 'instrumentos.nombre')
                    ->orderBy('instrumentos.nombre'),
            ])
            ->whereKey($idUsuario)
            ->first();

        $instrumentos = $usuarioInstrumentos?->instrumentos->pluck('nombre')->all() ?? [];

        $stats = DB::table('progreso')
            ->where('id_estudiante', $estudiante->id_estudiante)
            ->selectRaw('coalesce(sum(puntaje), 0) as puntos')
            ->selectRaw("count(*) filter (where estado = 'completado') as ejercicios_hechos")
            ->first();

        $gamificacion = $this->gamificacionData($estudiante->id_estudiante);
        $puntos = $gamificacion['xp_total'] ?: (int) round((float) ($stats->puntos ?? 0));
        $nivel = $gamificacion['nivel'] ?: max(1, intdiv($puntos, 500) + 1);
        $ranking = DB::table('ranking_estudiantes')
            ->where('id_estudiante', $estudiante->id_estudiante)
            ->first();

        return [
            'dashboardData' => [
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
                'foto' => $this->profilePhotoService->publicValue($estudiante->foto),
                'instrumento' => count($instrumentos) ? implode(', ', $instrumentos) : 'Sin instrumento',
                'nivel' => $nivel,
                'nivelTexto' => 'Nivel '.$nivel,
                'xp' => $puntos % 500,
                'xpMax' => 500,
                'puntos' => $puntos,
                'racha' => $gamificacion['racha_actual'],
                'ejerciciosHechos' => (int) ($stats->ejercicios_hechos ?? 0),
                'rankingPos' => $ranking ? (int) $ranking->posicion : 0,
                'seccion' => $estudiante->seccion ?: 'Sin seccion',
                'sede' => $estudiante->seccion ? 'Seccion '.$estudiante->seccion : 'Sin seccion',
                'profesor' => $estudiante->profesor_nombre ?: 'Sin profesor asignado',
                'miembroDesde' => $estudiante->fecha_ingreso
                    ? date('d/m/Y', strtotime($estudiante->fecha_ingreso))
                    : 'Sin fecha',
                'logrosDesbloqueados' => Estudiante::query()
                    ->whereKey($estudiante->id_estudiante)
                    ->withCount('recompensas')
                    ->first()?->recompensas_count ?? 0,
            ],
            'rankingData' => $this->rankingData($estudiante->id_estudiante),
            'tareasData' => $this->tareasData($estudiante->id_estudiante),
            'ejerciciosData' => $this->ejerciciosData(),
            'logrosData' => $this->logrosData($estudiante->id_estudiante),
            'actividadData' => $this->actividadData($estudiante->id_estudiante),
            'notificacionesData' => $this->notificacionesData($idUsuario),
            'cursosData' => $this->cursosData($estudiante->id_estudiante),
            'teoriaData' => $this->teoriaData(),
        ];
    }

    public function estudianteExiste(int $idUsuario): bool
    {
        return Estudiante::query()
            ->where('id_usuario', $idUsuario)
            ->exists();
    }

    public function updateProfile(int $idUsuario, array $validated): array
    {
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
            $payload['foto'] = $this->profilePhotoService->storeIfBase64($validated['foto']);
        }

        Usuario::query()
            ->whereKey($idUsuario)
            ->update($payload);

        if (array_key_exists('foto', $payload)) {
            $payload['foto'] = $this->profilePhotoService->publicValue($payload['foto']);
        }

        return $payload;
    }

    private function estudianteBase(int $idUsuario): ?object
    {
        $profesorNombreSql = DB::connection()->getDriverName() === 'pgsql'
            ? "concat_ws(' ', up.nombres, up.apellido_paterno) as profesor_nombre"
            : "trim(coalesce(up.nombres, '') || ' ' || coalesce(up.apellido_paterno, '')) as profesor_nombre";

        return DB::table('estudiantes as e')
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
                DB::raw($profesorNombreSql),
            ])
            ->first();
    }

    private function rankingData(int $idEstudiante): array
    {
        $instrumentosSql = DB::connection()->getDriverName() === 'pgsql'
            ? "coalesce(string_agg(i.nombre, ', ' order by i.nombre), 'Sin instrumento') as instrumentos"
            : "coalesce(group_concat(i.nombre, ', '), 'Sin instrumento') as instrumentos";

        return DB::table('ranking_estudiantes as r')
            ->join('estudiantes as e', 'e.id_estudiante', '=', 'r.id_estudiante')
            ->leftJoin('usuario_instrumento as ui', 'ui.id_usuario', '=', 'e.id_usuario')
            ->leftJoin('instrumentos as i', 'i.id_instrumento', '=', 'ui.id_instrumento')
            ->select([
                'r.id_estudiante',
                'r.nombres',
                'r.apellido_paterno',
                'r.puntaje_total',
                'r.posicion',
                DB::raw($instrumentosSql),
            ])
            ->groupBy('r.id_estudiante', 'r.nombres', 'r.apellido_paterno', 'r.puntaje_total', 'r.posicion')
            ->orderBy('r.posicion')
            ->limit(10)
            ->get()
            ->map(fn ($item) => [
                'nombre' => trim($item->nombres.' '.$item->apellido_paterno),
                'puntos' => (int) round((float) $item->puntaje_total),
                'avatar' => (int) $item->posicion <= 3 ? 'ðŸ†' : 'ðŸŽ“',
                'instrumento' => $item->instrumentos,
                'esYo' => (int) $item->id_estudiante === (int) $idEstudiante,
            ])
            ->all();
    }

    private function gamificacionData(int $idEstudiante): array
    {
        if (! Schema::hasTable('gamificacion_perfiles')) {
            return ['xp_total' => 0, 'nivel' => 0, 'racha_actual' => 0];
        }

        $perfil = DB::table('gamificacion_perfiles')->where('id_estudiante', $idEstudiante)->first();

        return [
            'xp_total' => (int) ($perfil->xp_total ?? 0),
            'nivel' => (int) ($perfil->nivel ?? 0),
            'racha_actual' => (int) ($perfil->racha_actual ?? 0),
        ];
    }

    private function tareasData(int $idEstudiante): array
    {
        if (! Schema::hasTable('tareas')) {
            return [];
        }

        return DB::table('tareas as t')
            ->leftJoin('profesores as p', 'p.id_profesor', '=', 't.id_profesor')
            ->leftJoin('usuarios as u', 'u.id_usuario', '=', 'p.id_usuario')
            ->leftJoin('entregas_tareas as et', function ($join) use ($idEstudiante) {
                $join->on('et.id_tarea', '=', 't.id_tarea')->where('et.id_estudiante', '=', $idEstudiante);
            })
            ->where('t.id_estudiante', $idEstudiante)
            ->select([
                't.id_tarea',
                't.titulo',
                't.descripcion',
                't.fecha_limite',
                't.xp_recompensa',
                DB::raw("coalesce(et.estado, t.estado, 'pendiente') as estado_tarea"),
                DB::raw("coalesce(u.nombres || ' ' || coalesce(u.apellido_paterno, ''), 'Profesor MSEA') as profesor"),
            ])
            ->orderByRaw('t.fecha_limite nulls last')
            ->get()
            ->map(fn ($tarea) => [
                'id' => $tarea->id_tarea,
                'nombre' => $tarea->titulo,
                'emoji' => 'T',
                'profesor' => trim($tarea->profesor),
                'vence' => $tarea->fecha_limite ? date('d/m/Y', strtotime($tarea->fecha_limite)) : 'Sin fecha',
                'urgencia' => $this->urgenciaTarea($tarea->fecha_limite),
                'xp' => (int) $tarea->xp_recompensa,
                'desc' => $tarea->descripcion ?: 'Practica asignada por tu profesor.',
                'tipo' => $tarea->estado_tarea ?: 'pendiente',
            ])
            ->all();
    }

    private function ejerciciosData(): array
    {
        if (! Schema::hasTable('ejercicios')) {
            return [];
        }

        return DB::table('ejercicios as e')
            ->join('tipos_ejercicio as te', 'te.id_tipo', '=', 'e.id_tipo')
            ->select(['e.id_ejercicio', 'e.titulo', 'e.descripcion', 'e.dificultad', 'e.xp', 'te.nombre as tipo'])
            ->orderBy('e.id_ejercicio')
            ->limit(12)
            ->get()
            ->map(fn ($ejercicio) => [
                'id' => $ejercicio->id_ejercicio,
                'emoji' => match (strtolower($ejercicio->tipo)) {
                    'ritmo' => 'R',
                    'teoria' => 'Te',
                    'afinacion' => 'Af',
                    default => 'M',
                },
                'nombre' => $ejercicio->titulo ?: ucfirst($ejercicio->tipo),
                'desc' => $ejercicio->descripcion ?: 'Ejercicio musical MSEA.',
                'chips' => [$ejercicio->tipo, ucfirst($ejercicio->dificultad ?? 'basico')],
                'xp' => (int) ($ejercicio->xp ?? 20),
                'tipo' => strtolower($ejercicio->tipo),
            ])
            ->all();
    }

    private function logrosData(int $idEstudiante): array
    {
        if (! Schema::hasTable('logros')) {
            return [];
        }

        $desbloqueados = Schema::hasTable('estudiante_logro')
            ? DB::table('estudiante_logro')->where('id_estudiante', $idEstudiante)->pluck('id_logro')->all()
            : [];

        return DB::table('logros')->orderBy('id_logro')->get()
            ->map(fn ($logro) => [
                'emoji' => in_array($logro->id_logro, $desbloqueados, true) ? 'OK' : 'X',
                'nombre' => $logro->nombre,
                'desc' => $logro->descripcion,
                'bloqueado' => ! in_array($logro->id_logro, $desbloqueados, true),
            ])
            ->all();
    }

    private function actividadData(int $idEstudiante): array
    {
        $items = collect();

        if (Schema::hasTable('progreso')) {
            $items = $items->merge(DB::table('progreso as p')
                ->leftJoin('ejercicios as e', 'e.id_ejercicio', '=', 'p.id_ejercicio')
                ->where('p.id_estudiante', $idEstudiante)
                ->select(['p.fecha', 'p.puntaje', 'e.titulo'])
                ->orderByDesc('p.fecha')
                ->limit(5)
                ->get()
                ->map(fn ($p) => [
                    'icono' => 'OK',
                    'texto' => 'Completaste: '.($p->titulo ?: 'Ejercicio'),
                    'tiempo' => $p->fecha ? date('d/m/Y H:i', strtotime($p->fecha)) : '',
                    'xp' => '+'.(int) $p->puntaje.' XP',
                    'orden' => $p->fecha,
                ]));
        }

        if (Schema::hasTable('practicas_autonomas')) {
            $items = $items->merge(DB::table('practicas_autonomas')
                ->where('id_estudiante', $idEstudiante)
                ->orderByDesc('created_at')
                ->limit(5)
                ->get()
                ->map(fn ($p) => [
                    'icono' => 'P',
                    'texto' => 'Practica autonoma: '.$p->tipo,
                    'tiempo' => date('d/m/Y H:i', strtotime($p->created_at)),
                    'xp' => '+'.(int) $p->xp_ganado.' XP',
                    'orden' => $p->created_at,
                ]));
        }

        return $items->sortByDesc('orden')->take(8)->values()->all();
    }

    private function notificacionesData(int $idUsuario): array
    {
        if (! Schema::hasTable('notificaciones')) {
            return [];
        }

        return DB::table('notificaciones')
            ->where('id_usuario', $idUsuario)
            ->orderByDesc('created_at')
            ->limit(8)
            ->get()
            ->map(fn ($n) => [
                'icono' => 'N',
                'texto' => $n->titulo.': '.$n->mensaje,
                'tiempo' => $n->created_at ? date('d/m/Y H:i', strtotime($n->created_at)) : '',
                'leida' => (bool) $n->leida_at,
            ])
            ->all();
    }

    private function cursosData(int $idEstudiante): array
    {
        if (! Schema::hasTable('cursos')) {
            return [];
        }

        return DB::table('curso_estudiante as ce')
            ->join('cursos as c', 'c.id_curso', '=', 'ce.id_curso')
            ->leftJoin('modulos as m', 'm.id_curso', '=', 'c.id_curso')
            ->leftJoin('lecciones as l', 'l.id_modulo', '=', 'm.id_modulo')
            ->where('ce.id_estudiante', $idEstudiante)
            ->select(['c.id_curso', 'c.titulo', 'c.nivel', 'c.instrumento', DB::raw('count(l.id_leccion) as lecciones')])
            ->groupBy('c.id_curso', 'c.titulo', 'c.nivel', 'c.instrumento')
            ->get()
            ->map(fn ($curso) => [
                'titulo' => $curso->titulo,
                'nivel' => $curso->nivel,
                'instrumento' => $curso->instrumento,
                'lecciones' => (int) $curso->lecciones,
            ])
            ->all();
    }

    private function teoriaData(): array
    {
        if (! Schema::hasTable('teoria_contenidos')) {
            return [];
        }

        return DB::table('teoria_contenidos')->orderBy('id_teoria')->limit(10)->get()
            ->map(fn ($t) => [
                'id' => $t->id_teoria,
                'titulo' => $t->titulo,
                'pregunta' => $t->pregunta,
                'opciones' => json_decode($t->opciones ?: '[]', true),
                'respuesta' => $t->respuesta_correcta,
                'xp' => (int) $t->xp,
            ])
            ->all();
    }

    private function urgenciaTarea(?string $fechaLimite): string
    {
        if (! $fechaLimite) {
            return 'normal';
        }

        $dias = now()->startOfDay()->diffInDays(date_create($fechaLimite), false);

        return $dias <= 1 ? 'urgente' : ($dias <= 4 ? 'normal' : 'proxima');
    }
}
