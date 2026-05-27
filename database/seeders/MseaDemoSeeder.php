<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class MseaDemoSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['estudiante', 'profesor', 'director'] as $rol) {
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

        foreach (['Violin', 'Viola', 'Chelo', 'Bajo'] as $instrumento) {
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
    }
}
