<?php

namespace App\Services;

use App\Models\Estudiante;
use App\Models\Instrumento;
use App\Models\Rol;
use App\Models\Seccion;
use App\Models\Usuario;
use App\Models\UsuarioInstrumento;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function findUsuarioConRol(string $correo): ?object
    {
        $usuario = Usuario::query()
            ->with('rol')
            ->where('correo', $correo)
            ->first();

        if ($usuario) {
            $usuario->setAttribute('rol_nombre', $usuario->rol?->nombre);
        }

        return $usuario;
    }

    public function contrasenaValida(string $contrasenaIngresada, object $usuario): bool
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

        if (hash_equals($contrasenaGuardada, $contrasenaIngresada)) {
            $this->actualizarContrasena($usuario->id_usuario, $contrasenaIngresada);

            return true;
        }

        return false;
    }

    public function registrarEstudiante(array $validated): int|false
    {
        $rol = Rol::query()->where('nombre', $validated['rol'])->first();

        if (! $rol) {
            return false;
        }

        return DB::transaction(function () use ($validated, $rol) {
            $usuario = Usuario::query()->create([
                'correo' => $validated['correo'],
                'contrasena' => Hash::make($validated['contrasena']),
                'nombres' => $validated['nombres'],
                'apellido_paterno' => $validated['apellido_paterno'],
                'apellido_materno' => $validated['apellido_materno'] ?? null,
                'id_rol' => $rol->id_rol,
            ]);

            if ($validated['rol'] === 'estudiante') {
                Estudiante::query()->create([
                    'id_usuario' => $usuario->id_usuario,
                    'fecha_ingreso' => now()->toDateString(),
                    'id_seccion' => $this->obtenerOCrearSeccionGeneral(),
                ]);

                if (! empty($validated['instrumento'])) {
                    UsuarioInstrumento::query()->firstOrCreate([
                        'id_usuario' => $usuario->id_usuario,
                        'id_instrumento' => $this->obtenerOCrearInstrumento($validated['instrumento']),
                    ]);
                }
            }

            return $usuario->id_usuario;
        });
    }

    private function obtenerOCrearSeccionGeneral(): int
    {
        $seccion = Seccion::query()->firstOrCreate([
            'nombre' => 'General',
        ]);

        return $seccion->id_seccion;
    }

    private function obtenerOCrearInstrumento(string $instrumento): int
    {
        $nombre = $this->normalizarInstrumento($instrumento);
        $instrumentoExistente = Instrumento::query()->firstOrCreate([
            'nombre' => $nombre,
        ]);

        return $instrumentoExistente->id_instrumento;
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

    private function actualizarContrasena(int $idUsuario, string $contrasena): void
    {
        Usuario::query()
            ->whereKey($idUsuario)
            ->update([
                'contrasena' => Hash::make($contrasena),
            ]);
    }
}
