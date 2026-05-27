<?php

namespace App\Services;

use App\Models\Estudiante;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;

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

        $puntos = (int) round((float) ($stats->puntos ?? 0));
        $nivel = max(1, intdiv($puntos, 500) + 1);
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
                'racha' => 0,
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
}
