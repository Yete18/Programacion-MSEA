<?php

namespace App\Services;

use App\Models\PasswordResetCode;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class PasswordResetService
{
    public function enviarCodigoSiUsuarioExiste(string $correo): void
    {
        $usuario = Usuario::query()->where('correo', $correo)->first();

        if (! $usuario) {
            return;
        }

        $codigo = (string) random_int(100000, 999999);

        PasswordResetCode::query()
            ->where('correo', $correo)
            ->whereNull('used_at')
            ->update(['used_at' => now()]);

        PasswordResetCode::query()->create([
            'correo' => $correo,
            'codigo' => Hash::make($codigo),
            'expires_at' => now()->addMinutes(15),
            'created_at' => now(),
        ]);

        Mail::raw(
            "Tu codigo de verificacion MSEA es: {$codigo}\n\nEste codigo expira en 15 minutos.",
            function ($message) use ($correo) {
                $message->to($correo)->subject('Codigo para restablecer tu contrasena MSEA');
            }
        );
    }

    public function verificarCodigo(string $correo, string $codigo): ?int
    {
        $codigos = PasswordResetCode::query()
            ->where('correo', $correo)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->orderByDesc('created_at')
            ->get();

        foreach ($codigos as $codigoGuardado) {
            if (Hash::check($codigo, $codigoGuardado->codigo)) {
                return $codigoGuardado->id;
            }
        }

        return null;
    }

    public function resetPassword(string $correo, int $codigoId, string $contrasena): bool
    {
        return DB::transaction(function () use ($correo, $codigoId, $contrasena) {
            $codigoActualizado = PasswordResetCode::query()
                ->whereKey($codigoId)
                ->where('correo', $correo)
                ->whereNull('used_at')
                ->update(['used_at' => now()]);

            if ($codigoActualizado !== 1) {
                return false;
            }

            Usuario::query()
                ->where('correo', $correo)
                ->update([
                    'contrasena' => Hash::make($contrasena),
                ]);

            return true;
        });
    }
}
