<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class DashboardAdminController extends Controller
{
    public function show()
    {
        if (session('rol') !== 'director') {
            return redirect('/login')->with('error', 'Inicia sesion como director para continuar');
        }

        return view('dashboard-admin', [
            'adminData' => $this->adminData(),
            'profesoresData' => $this->profesoresData(),
        ]);
    }

    public function storeProfesor(Request $request)
    {
        if (session('rol') !== 'director') {
            return redirect('/login')->with('error', 'Inicia sesion como director para continuar');
        }

        $validated = $request->validate([
            'nombres' => ['required', 'string', 'max:100'],
            'apellido_paterno' => ['required', 'string', 'max:100'],
            'apellido_materno' => ['nullable', 'string', 'max:100'],
            'correo' => ['required', 'email', 'max:100', 'unique:usuarios,correo'],
            'contrasena' => ['required', 'string', 'min:6', 'confirmed'],
            'ci' => ['nullable', 'string', 'max:20'],
            'celular' => ['nullable', 'string', 'max:20'],
            'direccion' => ['nullable', 'string', 'max:500'],
            'fecha_nacimiento' => ['nullable', 'date'],
            'especialidad' => ['nullable', 'string', 'max:50'],
        ]);

        $rolProfesor = DB::table('roles')->where('nombre', 'profesor')->first();

        if (! $rolProfesor) {
            return back()->withInput()->with('error', 'No existe el rol profesor en la base de datos.');
        }

        DB::transaction(function () use ($validated, $rolProfesor) {
            $idUsuario = DB::table('usuarios')->insertGetId([
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
            ], 'id_usuario');

            DB::table('profesores')->insert([
                'id_usuario' => $idUsuario,
            ]);

            if (! empty($validated['especialidad'])) {
                $idInstrumento = $this->obtenerOCrearInstrumento($validated['especialidad']);

                DB::table('usuario_instrumento')->insertOrIgnore([
                    'id_usuario' => $idUsuario,
                    'id_instrumento' => $idInstrumento,
                ]);
            }
        });

        return redirect('/dashboard-admin')->with('success', 'Profesor registrado correctamente.');
    }

    private function adminData(): array
    {
        $idUsuario = session('usuario_id');
        $usuario = DB::table('usuarios')->where('id_usuario', $idUsuario)->first();
        $totalProfesores = DB::table('profesores')->count();
        $totalEstudiantes = DB::table('estudiantes')->count();

        return [
            'nombre' => $usuario ? trim($usuario->nombres.' '.$usuario->apellido_paterno) : 'Director',
            'email' => $usuario->correo ?? 'Sin correo',
            'totalProfesores' => $totalProfesores,
            'totalEstudiantes' => $totalEstudiantes,
        ];
    }

    private function profesoresData(): array
    {
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
                DB::raw("coalesce(string_agg(distinct i.nombre, ', '), 'Sin especialidad') as especialidad"),
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
            ])
            ->all();
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

        $existente = DB::table('instrumentos')->where('nombre', $nombre)->first();

        if ($existente) {
            return $existente->id_instrumento;
        }

        return DB::table('instrumentos')->insertGetId([
            'nombre' => $nombre,
        ], 'id_instrumento');
    }
}
