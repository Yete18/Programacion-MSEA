<?php

namespace App\Services;

use App\Models\Elenco;
use App\Models\Estudiante;
use App\Models\Instrumento;
use App\Models\Profesor;
use App\Models\Rol;
use App\Models\TipoElenco;
use App\Models\Usuario;
use App\Models\UsuarioInstrumento;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class AdminDashboardService
{
    public function viewData(int $idUsuario): array
    {
        return [
            'adminData' => $this->adminData($idUsuario),
            'directorProfile' => $this->directorProfileData($idUsuario),
            'profesoresData' => $this->profesoresData(),
            'elencosData' => $this->elencosData(),
            'estudiantesData' => $this->estudiantesData(),
            'actividadData' => $this->actividadData(),
        ];
    }

    public function updateProfile(int $idUsuario, array $validated): bool
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

        if ($this->columnExists('usuarios', 'trayectoria')) {
            $payload['trayectoria'] = $validated['trayectoria'] ?? null;
        }

        Usuario::query()->whereKey($idUsuario)->update($payload);

        session([
            'nombre' => $payload['nombres'],
            'apellido_paterno' => $payload['apellido_paterno'],
            'correo' => $payload['correo'],
        ]);

        return $this->columnExists('usuarios', 'trayectoria');
    }

    public function storeProfesor(array $validated): bool
    {
        $rolProfesor = Rol::query()->where('nombre', 'profesor')->first();

        if (! $rolProfesor) {
            return false;
        }

        DB::transaction(function () use ($validated, $rolProfesor) {
            $usuario = Usuario::query()->create([
                'correo' => $validated['correo'],
                'contrasena' => Hash::make($validated['contrasena']),
                'nombres' => $validated['nombres'],
                'apellido_paterno' => $validated['apellido_paterno'],
                'apellido_materno' => $validated['apellido_materno'] ?? null,
                'ci' => $validated['ci'] ?? null,
                'celular' => $validated['celular'] ?? null,
                'direccion' => $validated['direccion'] ?? null,
                'fecha_nacimiento' => $validated['fecha_nacimiento'] ?? null,
                'id_rol' => $rolProfesor->id_rol,
            ]);

            Profesor::query()->create([
                'id_usuario' => $usuario->id_usuario,
            ]);

            if (! empty($validated['especialidad'])) {
                UsuarioInstrumento::query()->firstOrCreate([
                    'id_usuario' => $usuario->id_usuario,
                    'id_instrumento' => $this->obtenerOCrearInstrumento($validated['especialidad']),
                ]);
            }
        });

        return true;
    }

    public function storeElenco(array $validated): string|true
    {
        if (! $this->tableExists('tipos_elencos') || ! $this->tableExists('elencos')) {
            return 'Faltan las tablas de tipos_elencos o elencos.';
        }

        DB::transaction(function () use ($validated) {
            $idTipo = $this->obtenerOCrearTipoElenco($validated['tipo']);

            Elenco::query()->firstOrCreate([
                'nombre' => $validated['nombre'],
                'id_tipo' => $idTipo,
            ]);
        });

        return true;
    }

    public function updateElenco(int $idElenco, array $validated): void
    {
        DB::transaction(function () use ($validated, $idElenco) {
            Elenco::query()->whereKey($idElenco)->update([
                'nombre' => $validated['nombre'],
                'id_tipo' => $this->obtenerOCrearTipoElenco($validated['tipo']),
            ]);
        });
    }

    public function destroyElenco(int $idElenco): void
    {
        DB::transaction(function () use ($idElenco) {
            if ($this->columnExists('estudiantes', 'id_elenco')) {
                Estudiante::query()->where('id_elenco', $idElenco)->update([
                    'id_elenco' => null,
                ]);
            }

            Elenco::query()->whereKey($idElenco)->delete();
        });
    }

    public function assignStudentToElenco(int $idEstudiante, ?int $idElenco): string|true
    {
        if (! $this->columnExists('estudiantes', 'id_elenco')) {
            return 'Falta la columna estudiantes.id_elenco para asignar estudiantes.';
        }

        Estudiante::query()
            ->whereKey($idEstudiante)
            ->update([
                'id_elenco' => $idElenco,
            ]);

        return true;
    }

    public function assignStudentToProfesor(int $idEstudiante, ?int $idProfesor): string|true
    {
        if (! $this->columnExists('estudiantes', 'id_profesor')) {
            return 'Falta la columna estudiantes.id_profesor para asignar profesores.';
        }

        Estudiante::query()
            ->whereKey($idEstudiante)
            ->update([
                'id_profesor' => $idProfesor,
            ]);

        return true;
    }

    private function adminData(int $idUsuario): array
    {
        $usuario = Usuario::query()->whereKey($idUsuario)->first();

        return [
            'nombre' => $usuario ? trim($usuario->nombres.' '.$usuario->apellido_paterno) : 'Director',
            'email' => $usuario->correo ?? 'Sin correo',
            'totalProfesores' => $this->tableCount('profesores'),
            'totalEstudiantes' => $this->tableCount('estudiantes'),
            'totalElencos' => $this->tableCount('elencos'),
            'totalTareas' => $this->tableCount('tareas'),
            'totalEjercicios' => $this->tableCount('ejercicios'),
            'totalPracticas' => $this->tableCount('practicas'),
        ];
    }

    private function profesoresData(): array
    {
        if (! $this->tableExists('profesores') || ! $this->tableExists('usuarios')) {
            return [];
        }

        $estudiantesPorProfesor = $this->estudiantesPorProfesorData();
        $instrumentosSql = DB::connection()->getDriverName() === 'pgsql'
            ? "coalesce(string_agg(distinct i.nombre, ', '), 'Sin especialidad') as especialidad"
            : "coalesce(group_concat(distinct i.nombre), 'Sin especialidad') as especialidad";

        return DB::table('profesores as p')
            ->join('usuarios as u', 'u.id_usuario', '=', 'p.id_usuario')
            ->leftJoin('usuario_instrumento as ui', 'ui.id_usuario', '=', 'u.id_usuario')
            ->leftJoin('instrumentos as i', 'i.id_instrumento', '=', 'ui.id_instrumento')
            ->leftJoin('estudiantes as e', 'e.id_profesor', '=', 'p.id_profesor')
            ->select([
                'p.id_profesor',
                'u.nombres',
                'u.apellido_paterno',
                'u.apellido_materno',
                'u.correo',
                'u.celular',
                DB::raw($instrumentosSql),
                DB::raw('count(distinct e.id_estudiante) as estudiantes'),
            ])
            ->groupBy('p.id_profesor', 'u.nombres', 'u.apellido_paterno', 'u.apellido_materno', 'u.correo', 'u.celular')
            ->orderBy('u.apellido_paterno')
            ->get()
            ->map(fn ($profesor) => [
                'id' => $profesor->id_profesor,
                'nombre' => trim($profesor->nombres.' '.$profesor->apellido_paterno.' '.$profesor->apellido_materno),
                'correo' => $profesor->correo,
                'celular' => $profesor->celular ?: 'Sin celular',
                'especialidad' => $profesor->especialidad,
                'estudiantes' => (int) $profesor->estudiantes,
                'estudiantesLista' => $estudiantesPorProfesor[(int) $profesor->id_profesor] ?? [],
            ])
            ->all();
    }

    private function estudiantesPorProfesorData(): array
    {
        if (! $this->tableExists('estudiantes') || ! $this->tableExists('usuarios')) {
            return [];
        }

        $query = DB::table('estudiantes as e')
            ->join('usuarios as u', 'u.id_usuario', '=', 'e.id_usuario')
            ->whereNotNull('e.id_profesor')
            ->select(['e.id_profesor', 'e.id_estudiante', 'u.nombres', 'u.apellido_paterno', 'u.apellido_materno', 'u.correo']);

        if ($this->tableExists('elencos') && $this->columnExists('estudiantes', 'id_elenco')) {
            $query->leftJoin('elencos as el', 'el.id_elenco', '=', 'e.id_elenco')
                ->addSelect('el.nombre as elenco');
        } else {
            $query->addSelect(DB::raw("'Sin elenco' as elenco"));
        }

        return $query
            ->orderBy('e.id_profesor')
            ->orderBy('u.apellido_paterno')
            ->get()
            ->groupBy('id_profesor')
            ->map(fn ($estudiantes) => $estudiantes->map(fn ($estudiante) => [
                'id' => $estudiante->id_estudiante,
                'nombre' => trim($estudiante->nombres.' '.$estudiante->apellido_paterno.' '.$estudiante->apellido_materno),
                'correo' => $estudiante->correo,
                'elenco' => $estudiante->elenco ?: 'Sin elenco',
            ])->values()->all())
            ->all();
    }

    private function elencosData(): array
    {
        if (! $this->tableExists('elencos') || ! $this->tableExists('tipos_elencos')) {
            return [];
        }

        $query = DB::table('elencos as el')
            ->join('tipos_elencos as te', 'te.id_tipo', '=', 'el.id_tipo')
            ->select(['el.id_elenco', 'el.nombre', 'te.nombre as tipo']);

        if ($this->tableExists('estudiantes') && $this->columnExists('estudiantes', 'id_elenco')) {
            $query->leftJoin('estudiantes as e', 'e.id_elenco', '=', 'el.id_elenco')
                ->addSelect(DB::raw('count(e.id_estudiante) as estudiantes'))
                ->groupBy('el.id_elenco', 'el.nombre', 'te.nombre');
        } else {
            $query->addSelect(DB::raw('0 as estudiantes'));
        }

        return $query
            ->orderBy('te.nombre')
            ->orderBy('el.nombre')
            ->get()
            ->map(fn ($elenco) => [
                'id' => $elenco->id_elenco,
                'nombre' => $elenco->nombre,
                'tipo' => $elenco->tipo,
                'estudiantes' => (int) $elenco->estudiantes,
            ])
            ->all();
    }

    private function estudiantesData(): array
    {
        if (! $this->tableExists('estudiantes') || ! $this->tableExists('usuarios')) {
            return [];
        }

        $query = DB::table('estudiantes as e')
            ->join('usuarios as u', 'u.id_usuario', '=', 'e.id_usuario')
            ->select(['e.id_estudiante', 'u.nombres', 'u.apellido_paterno', 'u.apellido_materno', 'u.correo']);

        if ($this->tableExists('elencos') && $this->columnExists('estudiantes', 'id_elenco')) {
            $query->leftJoin('elencos as el', 'el.id_elenco', '=', 'e.id_elenco')
                ->addSelect('el.nombre as elenco');
        } else {
            $query->addSelect(DB::raw("'Sin elenco' as elenco"));
        }

        if ($this->tableExists('profesores')) {
            $query->leftJoin('profesores as p', 'p.id_profesor', '=', 'e.id_profesor')
                ->leftJoin('usuarios as up', 'up.id_usuario', '=', 'p.id_usuario')
                ->addSelect(DB::raw("coalesce(up.nombres || ' ' || coalesce(up.apellido_paterno, ''), 'Sin profesor') as profesor"));
        } else {
            $query->addSelect(DB::raw("'Sin profesor' as profesor"));
        }

        return $query
            ->orderBy('u.apellido_paterno')
            ->get()
            ->map(fn ($estudiante) => [
                'id' => $estudiante->id_estudiante,
                'nombre' => trim($estudiante->nombres.' '.$estudiante->apellido_paterno.' '.$estudiante->apellido_materno),
                'correo' => $estudiante->correo,
                'elenco' => $estudiante->elenco ?: 'Sin elenco',
                'profesor' => $estudiante->profesor ?: 'Sin profesor',
            ])
            ->all();
    }

    private function directorProfileData(int $idUsuario): array
    {
        $usuario = Usuario::query()->whereKey($idUsuario)->first();

        if (! $usuario) {
            return [
                'nombres' => '',
                'apellido_paterno' => '',
                'apellido_materno' => '',
                'correo' => '',
                'ci' => '',
                'celular' => '',
                'direccion' => '',
                'fecha_nacimiento' => '',
                'trayectoria' => '',
                'puedeGuardarTrayectoria' => false,
            ];
        }

        return [
            'nombres' => $usuario->nombres,
            'apellido_paterno' => $usuario->apellido_paterno,
            'apellido_materno' => $usuario->apellido_materno,
            'correo' => $usuario->correo,
            'ci' => $usuario->ci ?? '',
            'celular' => $usuario->celular ?? '',
            'direccion' => $usuario->direccion ?? '',
            'fecha_nacimiento' => $usuario->fecha_nacimiento ?? '',
            'trayectoria' => $this->columnExists('usuarios', 'trayectoria') ? ($usuario->trayectoria ?? '') : '',
            'puedeGuardarTrayectoria' => $this->columnExists('usuarios', 'trayectoria'),
        ];
    }

    private function actividadData(): array
    {
        return [
            'items' => collect()
                ->merge($this->actividadTareas())
                ->merge($this->actividadProgreso())
                ->merge($this->actividadPracticas())
                ->sortByDesc('fechaOrden')
                ->take(12)
                ->values()
                ->all(),
            'pendientesBackend' => [
                'Ultimo ingreso: agregar en usuarios un campo ultimo_ingreso_at o una tabla auditoria_accesos.',
                'Entregas de tareas: crear una tabla entregas_tareas para separar tarea asignada de tarea entregada.',
                'Archivos de entrega: relacionar entregas con archivos o enlaces enviados por estudiantes.',
            ],
        ];
    }

    private function actividadTareas(): array
    {
        if (! $this->tableExists('tareas')) {
            return [];
        }

        $query = DB::table('tareas as t')->select(['t.titulo', 't.fecha_creacion']);

        if ($this->tableExists('profesores') && $this->tableExists('usuarios')) {
            $query->leftJoin('profesores as p', 'p.id_profesor', '=', 't.id_profesor')
                ->leftJoin('usuarios as u', 'u.id_usuario', '=', 'p.id_usuario')
                ->addSelect(DB::raw("coalesce(u.nombres || ' ' || coalesce(u.apellido_paterno, ''), 'Profesor sin nombre') as actor"));
        } else {
            $query->addSelect(DB::raw("'Profesor sin nombre' as actor"));
        }

        return $query->orderByDesc('t.fecha_creacion')->limit(6)->get()
            ->map(fn ($tarea) => [
                'tipo' => 'Tarea enviada',
                'titulo' => $tarea->titulo,
                'detalle' => 'Asignada por '.$tarea->actor,
                'fecha' => $this->formatDate($tarea->fecha_creacion),
                'fechaOrden' => $tarea->fecha_creacion,
            ])->all();
    }

    private function actividadProgreso(): array
    {
        if (! $this->tableExists('progreso')) {
            return [];
        }

        $query = DB::table('progreso as pr')->select(['pr.estado', 'pr.puntaje', 'pr.precision', 'pr.fecha']);

        if ($this->tableExists('estudiantes') && $this->tableExists('usuarios')) {
            $query->leftJoin('estudiantes as e', 'e.id_estudiante', '=', 'pr.id_estudiante')
                ->leftJoin('usuarios as u', 'u.id_usuario', '=', 'e.id_usuario')
                ->addSelect(DB::raw("coalesce(u.nombres || ' ' || coalesce(u.apellido_paterno, ''), 'Estudiante sin nombre') as actor"));
        } else {
            $query->addSelect(DB::raw("'Estudiante sin nombre' as actor"));
        }

        return $query->orderByDesc('pr.fecha')->limit(6)->get()
            ->map(fn ($progreso) => [
                'tipo' => 'Ejercicio '.$progreso->estado,
                'titulo' => $progreso->actor,
                'detalle' => 'Puntaje: '.($progreso->puntaje ?? 0).' | Precision: '.($progreso->precision ?? 'sin dato'),
                'fecha' => $this->formatDate($progreso->fecha),
                'fechaOrden' => $progreso->fecha,
            ])->all();
    }

    private function actividadPracticas(): array
    {
        if (! $this->tableExists('practicas')) {
            return [];
        }

        $query = DB::table('practicas as p')->select(['p.tiempo', 'p.fecha']);

        if ($this->tableExists('estudiantes') && $this->tableExists('usuarios')) {
            $query->leftJoin('estudiantes as e', 'e.id_estudiante', '=', 'p.id_estudiante')
                ->leftJoin('usuarios as u', 'u.id_usuario', '=', 'e.id_usuario')
                ->addSelect(DB::raw("coalesce(u.nombres || ' ' || coalesce(u.apellido_paterno, ''), 'Estudiante sin nombre') as actor"));
        } else {
            $query->addSelect(DB::raw("'Estudiante sin nombre' as actor"));
        }

        return $query->orderByDesc('p.fecha')->limit(6)->get()
            ->map(fn ($practica) => [
                'tipo' => 'Practica registrada',
                'titulo' => $practica->actor,
                'detalle' => 'Tiempo: '.($practica->tiempo ?? 'sin tiempo registrado'),
                'fecha' => $this->formatDate($practica->fecha),
                'fechaOrden' => $practica->fecha,
            ])->all();
    }

    private function obtenerOCrearTipoElenco(string $tipo): int
    {
        $tipoElenco = TipoElenco::query()->firstOrCreate(['nombre' => $tipo]);

        return $tipoElenco->id_tipo;
    }

    private function obtenerOCrearInstrumento(string $instrumento): int
    {
        $nombre = match (strtolower(trim($instrumento))) {
            'violin' => 'Violin',
            'viola' => 'Viola',
            'chelo' => 'Chelo',
            'bajo' => 'Bajo',
            default => ucfirst(trim($instrumento)),
        };

        $instrumentoExistente = Instrumento::query()->firstOrCreate(['nombre' => $nombre]);

        return $instrumentoExistente->id_instrumento;
    }

    private function tableExists(string $table): bool
    {
        return Schema::hasTable($table);
    }

    private function columnExists(string $table, string $column): bool
    {
        return $this->tableExists($table) && Schema::hasColumn($table, $column);
    }

    private function tableCount(string $table): int
    {
        return $this->tableExists($table) ? DB::table($table)->count() : 0;
    }

    private function formatDate(?string $date): string
    {
        return $date ? date('d/m/Y H:i', strtotime($date)) : 'Sin fecha';
    }
}
