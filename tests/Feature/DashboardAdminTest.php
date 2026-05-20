<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DashboardAdminTest extends TestCase
{
    use DatabaseTransactions;

    public function test_director_puede_ver_panel_admin(): void
    {
        if (config('database.default') !== 'pgsql') {
            $this->markTestSkipped('Este flujo usa el esquema existente de PostgreSQL de MSEA.');
        }

        $idDirector = DB::table('usuarios')->insertGetId([
            'correo' => 'director.panel.'.time().'@msea.test',
            'contrasena' => Hash::make('secret123'),
            'nombres' => 'Director',
            'apellido_paterno' => 'Panel',
            'id_rol' => DB::table('roles')->where('nombre', 'director')->value('id_rol'),
        ], 'id_usuario');

        $this->withSession([
            'usuario_id' => $idDirector,
            'rol' => 'director',
        ])->get('/dashboard-admin')
            ->assertOk()
            ->assertSee('Gestión de profesores')
            ->assertSee('Registrar profesor');
    }

    public function test_director_puede_registrar_profesor(): void
    {
        if (config('database.default') !== 'pgsql') {
            $this->markTestSkipped('Este flujo usa el esquema existente de PostgreSQL de MSEA.');
        }

        $idDirector = DB::table('usuarios')->insertGetId([
            'correo' => 'director.crea.'.time().'@msea.test',
            'contrasena' => Hash::make('secret123'),
            'nombres' => 'Director',
            'apellido_paterno' => 'Creador',
            'id_rol' => DB::table('roles')->where('nombre', 'director')->value('id_rol'),
        ], 'id_usuario');

        $correoProfesor = 'prof.nuevo.'.time().'@msea.test';

        $this->withSession([
            'usuario_id' => $idDirector,
            'rol' => 'director',
        ])->post('/dashboard-admin/profesores', [
            'nombres' => 'Ana',
            'apellido_paterno' => 'Lopez',
            'apellido_materno' => 'Rojas',
            'correo' => $correoProfesor,
            'contrasena' => 'secret123',
            'contrasena_confirmation' => 'secret123',
            'ci' => '998877',
            'celular' => '70000000',
            'especialidad' => 'violin',
        ])->assertRedirect('/dashboard-admin');

        $usuario = DB::table('usuarios')->where('correo', $correoProfesor)->first();

        $this->assertNotNull($usuario);
        $this->assertTrue(Hash::check('secret123', $usuario->contrasena));
        $this->assertDatabaseHas('profesores', [
            'id_usuario' => $usuario->id_usuario,
        ]);
        $this->assertDatabaseHas('usuario_instrumento', [
            'id_usuario' => $usuario->id_usuario,
        ]);
    }

    public function test_estudiante_no_puede_ver_panel_admin(): void
    {
        $this->withSession([
            'usuario_id' => 1,
            'rol' => 'estudiante',
        ])->get('/dashboard-admin')
            ->assertRedirect('/login');
    }
}
