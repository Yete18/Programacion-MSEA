<?php

namespace Tests\Unit;

use App\Services\ProfilePhotoService;
use App\Services\StudentDashboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\Concerns\SeedsMseaCatalogs;
use Tests\TestCase;

class StudentDashboardServiceTest extends TestCase
{
    use RefreshDatabase;
    use SeedsMseaCatalogs;

    private StudentDashboardService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedMseaCatalogs();
        $this->service = new StudentDashboardService(new ProfilePhotoService());
    }

    public function test_dashboard_payload_calcula_datos_principales_del_estudiante(): void
    {
        $ids = $this->crearEstudianteConProgreso();

        $payload = $this->service->dashboardPayload($ids['usuario']);

        $this->assertNotNull($payload);
        $this->assertSame($ids['estudiante'], $payload['dashboardData']['idEstudiante']);
        $this->assertSame('Ana', $payload['dashboardData']['nombre']);
        $this->assertSame('Ana Lopez Rojas', $payload['dashboardData']['nombreCompleto']);
        $this->assertSame('Violin', $payload['dashboardData']['instrumento']);
        $this->assertSame(2, $payload['dashboardData']['nivel']);
        $this->assertSame(250, $payload['dashboardData']['xp']);
        $this->assertSame(750, $payload['dashboardData']['puntos']);
        $this->assertSame(1, $payload['dashboardData']['ejerciciosHechos']);
        $this->assertSame(1, $payload['dashboardData']['rankingPos']);
        $this->assertSame('General', $payload['dashboardData']['seccion']);
        $this->assertCount(1, $payload['rankingData']);
        $this->assertTrue($payload['rankingData'][0]['esYo']);
    }

    public function test_dashboard_payload_retorna_null_si_no_existe_estudiante(): void
    {
        $this->assertNull($this->service->dashboardPayload(999));
        $this->assertFalse($this->service->estudianteExiste(999));
    }

    public function test_update_profile_actualiza_usuario_y_retorna_payload_publico(): void
    {
        $ids = $this->crearEstudianteConProgreso();

        $payload = $this->service->updateProfile($ids['usuario'], [
            'nombres' => 'Ana Maria',
            'apellido_paterno' => 'Lopez',
            'apellido_materno' => 'Rojas',
            'correo' => 'ana.maria@msea.test',
            'ci' => '123456',
            'celular' => '76543210',
            'direccion' => 'Calle 1',
            'fecha_nacimiento' => '2005-04-01',
            'foto' => '/storage/avatars/ana.png',
        ]);

        $this->assertSame('Ana Maria', $payload['nombres']);
        $this->assertSame('/storage/avatars/ana.png', $payload['foto']);
        $this->assertDatabaseHas('usuarios', [
            'id_usuario' => $ids['usuario'],
            'correo' => 'ana.maria@msea.test',
            'celular' => '76543210',
        ]);
    }

    private function crearEstudianteConProgreso(): array
    {
        $idUsuario = DB::table('usuarios')->insertGetId([
            'correo' => 'ana-dashboard@msea.test',
            'contrasena' => Hash::make('secret123'),
            'nombres' => 'Ana',
            'apellido_paterno' => 'Lopez',
            'apellido_materno' => 'Rojas',
            'id_rol' => DB::table('roles')->where('nombre', 'estudiante')->value('id_rol'),
        ], 'id_usuario');

        $idEstudiante = DB::table('estudiantes')->insertGetId([
            'id_usuario' => $idUsuario,
            'fecha_ingreso' => '2026-05-01',
            'id_seccion' => DB::table('secciones')->where('nombre', 'General')->value('id_seccion'),
        ], 'id_estudiante');

        $idInstrumento = DB::table('instrumentos')->insertGetId([
            'nombre' => 'Violin',
        ], 'id_instrumento');

        DB::table('usuario_instrumento')->insert([
            'id_usuario' => $idUsuario,
            'id_instrumento' => $idInstrumento,
        ]);

        $idProfesorUsuario = DB::table('usuarios')->insertGetId([
            'correo' => 'profesor-dashboard@msea.test',
            'contrasena' => Hash::make('secret123'),
            'nombres' => 'Mario',
            'apellido_paterno' => 'Paz',
            'id_rol' => DB::table('roles')->where('nombre', 'profesor')->value('id_rol'),
        ], 'id_usuario');

        $idProfesor = DB::table('profesores')->insertGetId([
            'id_usuario' => $idProfesorUsuario,
        ], 'id_profesor');

        DB::table('estudiantes')->whereKey($idEstudiante)->update([
            'id_profesor' => $idProfesor,
        ]);

        $idTipoEjercicio = DB::table('tipos_ejercicio')->insertGetId([
            'nombre' => 'Ritmo',
        ], 'id_tipo');

        $idEjercicio = DB::table('ejercicios')->insertGetId([
            'id_tipo' => $idTipoEjercicio,
            'descripcion' => 'Ejercicio de ritmo',
            'creado_por' => $idProfesor,
        ], 'id_ejercicio');

        DB::table('progreso')->insert([
            'id_estudiante' => $idEstudiante,
            'id_ejercicio' => $idEjercicio,
            'puntaje' => 750,
            'estado' => 'completado',
            'fecha' => now(),
        ]);

        return [
            'usuario' => $idUsuario,
            'estudiante' => $idEstudiante,
        ];
    }
}
