<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class MseaDemoSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['estudiante', 'profesor', 'director', 'padre'] as $rol) {
            DB::table('roles')->updateOrInsert(
                ['nombre' => $rol],
                ['nombre' => $rol]
            );
        }

        foreach (['General', 'Inicial', 'Pre Juvenil', 'Juvenil'] as $seccion) {
            DB::table('secciones')->updateOrInsert(
                ['nombre' => $seccion],
                ['nombre' => $seccion]
            );
        }

        foreach (['Violin', 'Viola', 'Violonchelo', 'Contrabajo', 'Chelo', 'Bajo'] as $instrumento) {
            DB::table('instrumentos')->updateOrInsert(
                ['nombre' => $instrumento],
                ['nombre' => $instrumento]
            );
        }

        $tipos = [];
        foreach (['Orquesta', 'Elenco'] as $tipo) {
            DB::table('tipos_elencos')->updateOrInsert(
                ['nombre' => $tipo],
                ['nombre' => $tipo]
            );

            $tipos[$tipo] = DB::table('tipos_elencos')->where('nombre', $tipo)->value('id_tipo');
        }

        foreach ([
            ['nombre' => 'Inicial', 'id_tipo' => $tipos['Orquesta']],
            ['nombre' => 'Pre Juvenil', 'id_tipo' => $tipos['Orquesta']],
            ['nombre' => 'Juvenil', 'id_tipo' => $tipos['Orquesta']],
            ['nombre' => 'Elenco Inicial', 'id_tipo' => $tipos['Elenco']],
            ['nombre' => 'Elenco Pre Juvenil', 'id_tipo' => $tipos['Elenco']],
            ['nombre' => 'Elenco Juvenil', 'id_tipo' => $tipos['Elenco']],
        ] as $elenco) {
            $existe = DB::table('elencos')
                ->where('nombre', $elenco['nombre'])
                ->where('id_tipo', $elenco['id_tipo'])
                ->exists();

            if (! $existe) {
                DB::table('elencos')->insert($elenco);
            }
        }

        foreach (['Ritmo', 'Escala', 'Teoria', 'Afinacion', 'Repertorio'] as $tipoEjercicio) {
            DB::table('tipos_ejercicio')->updateOrInsert(
                ['nombre' => $tipoEjercicio],
                ['nombre' => $tipoEjercicio]
            );
        }

        $correoDirector = env('MSEA_DIRECTOR_EMAIL', 'director@msea.test');

        if (! DB::table('usuarios')->where('correo', $correoDirector)->exists()) {
            DB::table('usuarios')->insert([
                'correo' => $correoDirector,
                'contrasena' => Hash::make(env('MSEA_DIRECTOR_PASSWORD', 'director123')),
                'nombres' => env('MSEA_DIRECTOR_NAME', 'Director'),
                'apellido_paterno' => env('MSEA_DIRECTOR_LASTNAME', 'MSEA'),
                'id_rol' => DB::table('roles')->where('nombre', 'director')->value('id_rol'),
            ]);
        }

        $idProfesor = $this->ensureProfesorDemo();
        $idEstudiante = $this->ensureEstudianteDemo($idProfesor);
        $this->ensurePadreDemo($idEstudiante);
        $this->ensureGamificationCatalog($idEstudiante);
        $this->ensureAcademicDemo($idProfesor, $idEstudiante);
        $this->ensureTaskDemo($idProfesor, $idEstudiante);
    }

    private function ensureProfesorDemo(): int
    {
        $correo = 'profesor@msea.test';
        $idUsuario = DB::table('usuarios')->where('correo', $correo)->value('id_usuario');

        if (! $idUsuario) {
            $idUsuario = DB::table('usuarios')->insertGetId([
                'correo' => $correo,
                'contrasena' => Hash::make('profesor123'),
                'nombres' => 'Ana',
                'apellido_paterno' => 'Quispe',
                'celular' => '70000001',
                'id_rol' => DB::table('roles')->where('nombre', 'profesor')->value('id_rol'),
            ], 'id_usuario');
        }

        DB::table('profesores')->updateOrInsert(['id_usuario' => $idUsuario], ['id_usuario' => $idUsuario]);
        $idProfesor = DB::table('profesores')->where('id_usuario', $idUsuario)->value('id_profesor');
        $idInstrumento = DB::table('instrumentos')->where('nombre', 'Violin')->value('id_instrumento');

        DB::table('usuario_instrumento')->updateOrInsert([
            'id_usuario' => $idUsuario,
            'id_instrumento' => $idInstrumento,
        ]);

        return (int) $idProfesor;
    }

    private function ensureEstudianteDemo(int $idProfesor): int
    {
        $correo = 'estudiante@msea.test';
        $idUsuario = DB::table('usuarios')->where('correo', $correo)->value('id_usuario');

        if (! $idUsuario) {
            $idUsuario = DB::table('usuarios')->insertGetId([
                'correo' => $correo,
                'contrasena' => Hash::make('estudiante123'),
                'nombres' => 'Carlos',
                'apellido_paterno' => 'Mamani',
                'apellido_materno' => 'Choque',
                'fecha_nacimiento' => '2014-04-12',
                'id_rol' => DB::table('roles')->where('nombre', 'estudiante')->value('id_rol'),
            ], 'id_usuario');
        }

        DB::table('estudiantes')->updateOrInsert(
            ['id_usuario' => $idUsuario],
            [
                'fecha_ingreso' => now()->subMonths(3)->toDateString(),
                'id_profesor' => $idProfesor,
                'id_seccion' => DB::table('secciones')->where('nombre', 'Inicial')->value('id_seccion')
                    ?: DB::table('secciones')->where('nombre', 'General')->value('id_seccion'),
                'id_elenco' => DB::table('elencos')->where('nombre', 'Inicial')->value('id_elenco'),
                'monto_pago' => 0,
            ]
        );

        $idEstudiante = DB::table('estudiantes')->where('id_usuario', $idUsuario)->value('id_estudiante');
        $idInstrumento = DB::table('instrumentos')->where('nombre', 'Violin')->value('id_instrumento');

        DB::table('usuario_instrumento')->updateOrInsert([
            'id_usuario' => $idUsuario,
            'id_instrumento' => $idInstrumento,
        ]);

        return (int) $idEstudiante;
    }

    private function ensurePadreDemo(int $idEstudiante): int
    {
        if (! Schema::hasTable('padres')) {
            return 0;
        }

        $correo = 'padre@msea.test';
        $idUsuario = DB::table('usuarios')->where('correo', $correo)->value('id_usuario');

        if (! $idUsuario) {
            $idUsuario = DB::table('usuarios')->insertGetId([
                'correo' => $correo,
                'contrasena' => Hash::make('padre123'),
                'nombres' => 'Rosa',
                'apellido_paterno' => 'Choque',
                'celular' => '70000002',
                'id_rol' => DB::table('roles')->where('nombre', 'padre')->value('id_rol'),
            ], 'id_usuario');
        }

        DB::table('padres')->updateOrInsert(
            ['id_usuario' => $idUsuario],
            ['parentesco' => 'Madre', 'updated_at' => now(), 'created_at' => now()]
        );

        $idPadre = DB::table('padres')->where('id_usuario', $idUsuario)->value('id_padre');

        DB::table('estudiante_padre')->updateOrInsert(
            ['id_estudiante' => $idEstudiante, 'id_padre' => $idPadre],
            ['updated_at' => now(), 'created_at' => now()]
        );

        return (int) $idPadre;
    }

    private function ensureGamificationCatalog(int $idEstudiante): void
    {
        if (! Schema::hasTable('logros')) {
            return;
        }

        foreach ([
            ['codigo' => 'primer_paso', 'nombre' => 'Primer paso musical', 'descripcion' => 'Completa tu primera actividad.', 'xp_bonus' => 10],
            ['codigo' => '500_xp', 'nombre' => '500 XP', 'descripcion' => 'Acumula 500 puntos de experiencia.', 'xp_bonus' => 25],
            ['codigo' => 'nivel_4', 'nombre' => 'Nivel 4', 'descripcion' => 'Alcanza el nivel 4.', 'xp_bonus' => 50],
            ['codigo' => 'racha_3', 'nombre' => 'Racha de 3 dias', 'descripcion' => 'Practica tres dias seguidos.', 'xp_bonus' => 20],
            ['codigo' => 'racha_7', 'nombre' => 'Semana perfecta', 'descripcion' => 'Practica siete dias seguidos.', 'xp_bonus' => 100],
        ] as $logro) {
            DB::table('logros')->updateOrInsert(['codigo' => $logro['codigo']], $logro);
        }

        DB::table('gamificacion_perfiles')->updateOrInsert(
            ['id_estudiante' => $idEstudiante],
            ['xp_total' => 180, 'nivel' => 1, 'racha_actual' => 2, 'mejor_racha' => 2, 'ultima_practica' => now()->toDateString()]
        );
    }

    private function ensureAcademicDemo(int $idProfesor, int $idEstudiante): void
    {
        if (! Schema::hasTable('cursos')) {
            return;
        }

        DB::table('cursos')->updateOrInsert(
            ['titulo' => 'Violin Inicial MSEA'],
            [
                'descripcion' => 'Fundamentos de postura, lectura y ritmo para estudiantes iniciales.',
                'instrumento' => 'Violin',
                'nivel' => 'Inicial',
                'id_profesor' => $idProfesor,
                'activo' => true,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        $idCurso = DB::table('cursos')->where('titulo', 'Violin Inicial MSEA')->value('id_curso');

        DB::table('modulos')->updateOrInsert(
            ['id_curso' => $idCurso, 'orden' => 1],
            ['titulo' => 'Postura y sonido', 'descripcion' => 'Primer contacto con el instrumento.', 'updated_at' => now(), 'created_at' => now()]
        );

        $idModulo = DB::table('modulos')->where('id_curso', $idCurso)->where('orden', 1)->value('id_modulo');

        foreach ([
            ['orden' => 1, 'titulo' => 'Partes del violin', 'tipo' => 'teoria', 'contenido' => 'Identifica clavijas, puente, cuerdas y arco.', 'xp' => 15],
            ['orden' => 2, 'titulo' => 'Ritmo 4/4 con palmas', 'tipo' => 'ritmo', 'contenido' => 'Marca pulso estable con metronomo.', 'xp' => 20],
            ['orden' => 3, 'titulo' => 'Afinacion de cuerdas al aire', 'tipo' => 'afinacion', 'contenido' => 'Reconoce Sol, Re, La y Mi.', 'xp' => 20],
        ] as $leccion) {
            DB::table('lecciones')->updateOrInsert(
                ['id_modulo' => $idModulo, 'orden' => $leccion['orden']],
                array_merge($leccion, ['id_modulo' => $idModulo, 'updated_at' => now(), 'created_at' => now()])
            );
        }

        DB::table('curso_estudiante')->updateOrInsert([
            'id_curso' => $idCurso,
            'id_estudiante' => $idEstudiante,
        ]);

        $idLeccion = DB::table('lecciones')->where('id_modulo', $idModulo)->where('orden', 1)->value('id_leccion');
        DB::table('teoria_contenidos')->updateOrInsert(
            ['titulo' => 'Cuerda mas aguda del violin'],
            [
                'id_leccion' => $idLeccion,
                'pregunta' => 'Cual es la cuerda mas aguda del violin?',
                'opciones' => json_encode(['Sol', 'Re', 'La', 'Mi']),
                'respuesta_correcta' => 'Mi',
                'nivel' => 'Inicial',
                'xp' => 10,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }

    private function ensureTaskDemo(int $idProfesor, int $idEstudiante): void
    {
        $idTipo = DB::table('tipos_ejercicio')->where('nombre', 'Escala')->value('id_tipo');
        DB::table('ejercicios')->updateOrInsert(
            ['titulo' => 'Escala de Re Mayor'],
            [
                'id_tipo' => $idTipo,
                'descripcion' => 'Toca la escala de Re Mayor en primera posicion con arco separado.',
                'creado_por' => $idProfesor,
                'dificultad' => 'basico',
                'xp' => 30,
            ]
        );

        $idEjercicio = DB::table('ejercicios')->where('titulo', 'Escala de Re Mayor')->value('id_ejercicio');

        DB::table('tareas')->updateOrInsert(
            ['titulo' => 'Practicar escala de Re Mayor', 'id_estudiante' => $idEstudiante],
            [
                'descripcion' => 'Graba o registra tu practica diaria de la escala.',
                'fecha_creacion' => now(),
                'id_profesor' => $idProfesor,
                'id_elenco' => null,
                'fecha_limite' => now()->addDays(5)->toDateString(),
                'xp_recompensa' => 30,
                'estado' => 'pendiente',
            ]
        );

        $idTarea = DB::table('tareas')->where('titulo', 'Practicar escala de Re Mayor')->where('id_estudiante', $idEstudiante)->value('id_tarea');
        DB::table('tarea_ejercicio')->updateOrInsert(['id_tarea' => $idTarea, 'id_ejercicio' => $idEjercicio]);

        if (Schema::hasTable('notificaciones')) {
            $idUsuario = DB::table('estudiantes')->where('id_estudiante', $idEstudiante)->value('id_usuario');
            DB::table('notificaciones')->updateOrInsert(
                ['id_usuario' => $idUsuario, 'titulo' => 'Nueva tarea asignada'],
                ['mensaje' => 'Practicar escala de Re Mayor antes de la siguiente clase.', 'tipo' => 'tarea', 'updated_at' => now(), 'created_at' => now()]
            );
        }
    }
}
