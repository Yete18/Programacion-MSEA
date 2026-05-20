<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthFlowTest extends TestCase
{
    use DatabaseTransactions;

    public function test_estudiante_puede_registrarse_e_ingresar_al_dashboard(): void
    {
        if (config('database.default') !== 'pgsql') {
            $this->markTestSkipped('Este flujo usa el esquema existente de PostgreSQL de MSEA.');
        }

        $correo = 'estudiante.test.'.time().'@msea.test';

        $this->post('/registro', [
            'rol' => 'estudiante',
            'nombres' => 'Estudiante',
            'apellido_paterno' => 'Prueba',
            'usuario' => 'estudiante_prueba',
            'correo' => $correo,
            'instrumento' => 'violin',
            'nivel' => 'principiante',
            'contrasena' => 'secret123',
            'contrasena_confirmation' => 'secret123',
        ])->assertRedirect('/dashboard-estudiante');

        $this->assertDatabaseHas('usuarios', [
            'correo' => $correo,
            'nombres' => 'Estudiante',
            'apellido_paterno' => 'Prueba',
        ]);

        $usuario = DB::table('usuarios')->where('correo', $correo)->first();

        $this->assertDatabaseHas('estudiantes', [
            'id_usuario' => $usuario->id_usuario,
        ]);

        $this->assertDatabaseHas('instrumentos', [
            'nombre' => 'Violin',
        ]);

        $this->assertDatabaseHas('usuario_instrumento', [
            'id_usuario' => $usuario->id_usuario,
        ]);

        $this->post('/logout')->assertRedirect('/login');

        $this->post('/login', [
            'rol' => 'estudiante',
            'correo' => $correo,
            'contrasena' => 'secret123',
        ])->assertRedirect('/dashboard-estudiante');
    }

    public function test_estudiante_con_contrasena_antigua_puede_iniciar_sesion_y_se_actualiza(): void
    {
        if (config('database.default') !== 'pgsql') {
            $this->markTestSkipped('Este flujo usa el esquema existente de PostgreSQL de MSEA.');
        }

        $correo = 'legacy.test.'.time().'@msea.test';
        $rol = DB::table('roles')->where('nombre', 'estudiante')->first();
        $seccion = DB::table('secciones')->where('nombre', 'General')->first();

        if (! $seccion) {
            $idSeccion = DB::table('secciones')->insertGetId([
                'nombre' => 'General',
            ], 'id_seccion');
        } else {
            $idSeccion = $seccion->id_seccion;
        }

        $idUsuario = DB::table('usuarios')->insertGetId([
            'correo' => $correo,
            'contrasena' => '1234',
            'nombres' => 'Legacy',
            'apellido_paterno' => 'Prueba',
            'id_rol' => $rol->id_rol,
        ], 'id_usuario');

        DB::table('estudiantes')->insert([
            'id_usuario' => $idUsuario,
            'id_seccion' => $idSeccion,
            'fecha_ingreso' => now()->toDateString(),
        ]);

        $this->post('/login', [
            'rol' => 'estudiante',
            'correo' => $correo,
            'contrasena' => '1234',
        ])->assertRedirect('/dashboard-estudiante');

        $contrasenaActualizada = DB::table('usuarios')
            ->where('id_usuario', $idUsuario)
            ->value('contrasena');

        $this->assertNotSame('1234', $contrasenaActualizada);
        $this->assertTrue(Hash::check('1234', $contrasenaActualizada));
    }

    public function test_registro_publico_no_permite_crear_profesores(): void
    {
        if (config('database.default') !== 'pgsql') {
            $this->markTestSkipped('Este flujo usa el esquema existente de PostgreSQL de MSEA.');
        }

        $correo = 'profesor.publico.'.time().'@msea.test';

        $this->post('/registro', [
            'rol' => 'profesor',
            'nombres' => 'Profesor',
            'apellido_paterno' => 'Publico',
            'correo' => $correo,
            'contrasena' => 'secret123',
            'contrasena_confirmation' => 'secret123',
        ])->assertSessionHasErrors('rol');

        $this->assertDatabaseMissing('usuarios', [
            'correo' => $correo,
        ]);
    }
}
