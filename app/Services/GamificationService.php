<?php

namespace App\Services;

use App\Models\GamificacionPerfil;
use App\Models\Logro;
use App\Models\Notificacion;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class GamificationService
{
    public function awardXp(int $idEstudiante, int $xp, string $reason = 'Progreso registrado'): void
    {
        if (! Schema::hasTable('gamificacion_perfiles')) {
            return;
        }

        DB::transaction(function () use ($idEstudiante, $xp, $reason) {
            $perfil = GamificacionPerfil::query()->firstOrCreate(
                ['id_estudiante' => $idEstudiante],
                ['xp_total' => 0, 'nivel' => 1]
            );

            $today = now()->toDateString();
            $yesterday = now()->subDay()->toDateString();
            $racha = match ((string) $perfil->ultima_practica?->toDateString()) {
                $today => $perfil->racha_actual,
                $yesterday => $perfil->racha_actual + 1,
                default => 1,
            };

            $perfil->xp_total += max(0, $xp);
            $perfil->nivel = $this->levelForXp((int) $perfil->xp_total);
            $perfil->racha_actual = $racha;
            $perfil->mejor_racha = max((int) $perfil->mejor_racha, $racha);
            $perfil->ultima_practica = $today;
            $perfil->save();

            $this->unlockAchievements($idEstudiante, (int) $perfil->xp_total, $racha);
            $this->notifyStudent($idEstudiante, 'XP ganado', $reason.' (+'.$xp.' XP)', 'xp');
        });
    }

    public function levelForXp(int $xp): int
    {
        return max(1, intdiv($xp, 500) + 1);
    }

    private function unlockAchievements(int $idEstudiante, int $xpTotal, int $racha): void
    {
        if (! Schema::hasTable('logros') || ! Schema::hasTable('estudiante_logro')) {
            return;
        }

        $codes = ['primer_paso'];
        if ($xpTotal >= 500) {
            $codes[] = '500_xp';
        }
        if ($xpTotal >= 1500) {
            $codes[] = 'nivel_4';
        }
        if ($racha >= 3) {
            $codes[] = 'racha_3';
        }
        if ($racha >= 7) {
            $codes[] = 'racha_7';
        }

        $logros = Logro::query()->whereIn('codigo', $codes)->get();

        foreach ($logros as $logro) {
            DB::table('estudiante_logro')->updateOrInsert(
                ['id_estudiante' => $idEstudiante, 'id_logro' => $logro->id_logro],
                ['desbloqueado_at' => now()]
            );
        }
    }

    private function notifyStudent(int $idEstudiante, string $titulo, string $mensaje, string $tipo): void
    {
        if (! Schema::hasTable('notificaciones')) {
            return;
        }

        $idUsuario = DB::table('estudiantes')->where('id_estudiante', $idEstudiante)->value('id_usuario');

        if (! $idUsuario) {
            return;
        }

        Notificacion::query()->create([
            'id_usuario' => $idUsuario,
            'titulo' => $titulo,
            'mensaje' => $mensaje,
            'tipo' => $tipo,
        ]);
    }
}
