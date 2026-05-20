<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'correo' => 'required|email',
            'contrasena' => 'required',
            'rol' => ['required', Rule::in(['estudiante', 'profesor', 'director', 'admin'])],
        ]);

        $rolSolicitado = $request->rol === 'admin' ? 'director' : $request->rol;

        $usuario = DB::table('usuarios')
            ->join('roles', 'usuarios.id_rol', '=', 'roles.id_rol')
            ->select('usuarios.*', 'roles.nombre as rol_nombre')
            ->where('usuarios.correo', $request->correo)
            ->first();

        if (! $usuario) {
            return back()
                ->withInput($request->only('correo', 'rol'))
                ->with('error', 'Correo no encontrado');
        }

        if (! $this->contrasenaValida($request->contrasena, $usuario)) {
            return back()
                ->withInput($request->only('correo', 'rol'))
                ->with('error', 'Contrasena incorrecta');
        }

        if ($usuario->rol_nombre !== $rolSolicitado) {
            return back()
                ->withInput($request->only('correo', 'rol'))
                ->with('error', 'El rol seleccionado no corresponde a este usuario');
        }

        $request->session()->regenerate();

        session([
            'usuario_id' => $usuario->id_usuario,
            'nombre' => $usuario->nombres,
            'apellido_paterno' => $usuario->apellido_paterno,
            'correo' => $usuario->correo,
            'rol' => $usuario->rol_nombre,
        ]);

        return match ($usuario->rol_nombre) {
            'estudiante' => redirect('/dashboard-estudiante'),
            'profesor' => redirect('/dashboard-profesor'),
            'director' => redirect('/dashboard-admin'),
            default => redirect('/'),
        };
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'correo' => 'required|email|unique:usuarios,correo',
            'contrasena' => 'required|min:6|confirmed',
            'nombres' => 'required|string|max:100',
            'apellido_paterno' => 'required|string|max:100',
            'apellido_materno' => 'nullable|string|max:100',
            'instrumento' => 'nullable|string|max:50',
            'rol' => ['required', Rule::in(['estudiante'])],
        ]);

        $rol = DB::table('roles')->where('nombre', $validated['rol'])->first();

        if (! $rol) {
            return back()->withInput()->with('error', 'Rol no valido');
        }

        $idUsuario = DB::transaction(function () use ($validated, $rol) {
            $idUsuario = DB::table('usuarios')->insertGetId([
                'correo' => $validated['correo'],
                'contrasena' => Hash::make($validated['contrasena']),
                'nombres' => $validated['nombres'],
                'apellido_paterno' => $validated['apellido_paterno'],
                'apellido_materno' => $validated['apellido_materno'] ?? null,
                'id_rol' => $rol->id_rol,
            ], 'id_usuario');

            if ($validated['rol'] === 'estudiante') {
                $idSeccion = $this->obtenerOCrearSeccionGeneral();

                DB::table('estudiantes')->insert([
                    'id_usuario' => $idUsuario,
                    'fecha_ingreso' => now()->toDateString(),
                    'id_seccion' => $idSeccion,
                ]);

                if (! empty($validated['instrumento'])) {
                    $idInstrumento = $this->obtenerOCrearInstrumento($validated['instrumento']);

                    DB::table('usuario_instrumento')->insertOrIgnore([
                        'id_usuario' => $idUsuario,
                        'id_instrumento' => $idInstrumento,
                    ]);
                }
            }

            return $idUsuario;
        });

        $request->session()->regenerate();

        session([
            'usuario_id' => $idUsuario,
            'nombre' => $validated['nombres'],
            'apellido_paterno' => $validated['apellido_paterno'],
            'correo' => $validated['correo'],
            'rol' => $validated['rol'],
        ]);

        return redirect('/dashboard-estudiante');
    }

    public function logout(Request $request)
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }

    private function obtenerOCrearSeccionGeneral(): int
    {
        $seccion = DB::table('secciones')->where('nombre', 'General')->first();

        if ($seccion) {
            return $seccion->id_seccion;
        }

        return DB::table('secciones')->insertGetId([
            'nombre' => 'General',
        ], 'id_seccion');
    }

    private function obtenerOCrearInstrumento(string $instrumento): int
    {
        $nombre = $this->normalizarInstrumento($instrumento);
        $instrumentoExistente = DB::table('instrumentos')->where('nombre', $nombre)->first();

        if ($instrumentoExistente) {
            return $instrumentoExistente->id_instrumento;
        }

        return DB::table('instrumentos')->insertGetId([
            'nombre' => $nombre,
        ], 'id_instrumento');
    }

    private function normalizarInstrumento(string $instrumento): string
    {
        return match (strtolower($instrumento)) {
            'violin' => 'Violin',
            'viola' => 'Viola',
            'chelo' => 'Chelo',
            'bajo' => 'Bajo',
            default => ucfirst($instrumento),
        };
    }

    private function contrasenaValida(string $contrasenaIngresada, object $usuario): bool
    {
        $contrasenaGuardada = (string) $usuario->contrasena;
        if (str_starts_with($contrasenaGuardada, '$2y$')) {
            if (Hash::check($contrasenaIngresada, $contrasenaGuardada)) {
                if (Hash::needsRehash($contrasenaGuardada)) {
                    $this->actualizarContrasena($usuario->id_usuario, $contrasenaIngresada);
                }

                return true;
            }
        }

        // Compatibilidad temporal para usuarios creados antes de usar Hash::make().
        if (hash_equals($contrasenaGuardada, $contrasenaIngresada)) {
            $this->actualizarContrasena($usuario->id_usuario, $contrasenaIngresada);

            return true;
        }

        return false;
    }

    private function actualizarContrasena(int $idUsuario, string $contrasena): void
    {
        DB::table('usuarios')
            ->where('id_usuario', $idUsuario)
            ->update([
                'contrasena' => Hash::make($contrasena),
            ]);
    }
}
