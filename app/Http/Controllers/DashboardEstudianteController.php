<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateStudentProfileRequest;
use App\Services\GamificationService;
use App\Services\StudentDashboardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardEstudianteController extends Controller
{
    public function show(StudentDashboardService $studentDashboardService)
    {
        if (session('rol') !== 'estudiante') {
            return redirect('/login')->with('error', 'Inicia sesion como estudiante para continuar');
        }

        $payload = $studentDashboardService->dashboardPayload((int) session('usuario_id'));

        if (! $payload) {
            session()->invalidate();

            return redirect('/login')->with('error', 'No encontramos el perfil de estudiante para este usuario');
        }

        return view('dashboard-estudiante', $payload);
    }

    public function updateProfile(UpdateStudentProfileRequest $request, StudentDashboardService $studentDashboardService)
    {
        if (session('rol') !== 'estudiante') {
            return response()->json([
                'message' => 'Inicia sesion como estudiante para continuar',
            ], 401);
        }

        $idUsuario = (int) session('usuario_id');

        if (! $studentDashboardService->estudianteExiste($idUsuario)) {
            return response()->json([
                'message' => 'No encontramos el perfil de estudiante para este usuario',
            ], 404);
        }

        $payload = $studentDashboardService->updateProfile($idUsuario, $request->validated());

        session([
            'nombre' => $payload['nombres'],
            'apellido_paterno' => $payload['apellido_paterno'],
            'correo' => $payload['correo'],
        ]);

        return response()->json([
            'message' => 'Perfil actualizado correctamente',
            'estudiante' => [
                'nombre' => $payload['nombres'],
                'apellidoPaterno' => $payload['apellido_paterno'],
                'apellidoMaterno' => $payload['apellido_materno'],
                'apellido' => trim(($payload['apellido_paterno'] ?? '').' '.($payload['apellido_materno'] ?? '')),
                'nombreCompleto' => trim($payload['nombres'].' '.($payload['apellido_paterno'] ?? '').' '.($payload['apellido_materno'] ?? '')),
                'email' => $payload['correo'],
                'ci' => $payload['ci'],
                'celular' => $payload['celular'],
                'direccion' => $payload['direccion'],
                'fechaNacimiento' => $payload['fecha_nacimiento'],
                'foto' => $payload['foto'] ?? null,
            ],
        ]);
    }

    public function recordPractice(Request $request, GamificationService $gamificationService)
    {
        if (session('rol') !== 'estudiante') {
            return response()->json(['message' => 'Inicia sesion como estudiante para continuar'], 401);
        }

        $validated = $request->validate([
            'tipo' => ['required', 'string', 'max:40'],
            'duracion_segundos' => ['nullable', 'integer', 'min:0', 'max:7200'],
            'id_ejercicio' => ['nullable', 'integer'],
            'precision' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'nota_objetivo' => ['nullable', 'string', 'max:10'],
            'nota_detectada' => ['nullable', 'string', 'max:10'],
            'frecuencia' => ['nullable', 'numeric', 'min:0', 'max:5000'],
            'desviacion_cents' => ['nullable', 'numeric', 'min:-200', 'max:200'],
            'bpm' => ['nullable', 'integer', 'min:30', 'max:240'],
            'patron' => ['nullable', 'string', 'max:100'],
        ]);

        $estudiante = DB::table('estudiantes')->where('id_usuario', session('usuario_id'))->first();

        if (! $estudiante) {
            return response()->json(['message' => 'No encontramos el perfil de estudiante'], 404);
        }

        $xp = $this->xpForPractice($validated);

        DB::transaction(function () use ($validated, $estudiante, $xp, $gamificationService) {
            if (Schema::hasTable('practicas_autonomas')) {
                DB::table('practicas_autonomas')->insert([
                    'id_estudiante' => $estudiante->id_estudiante,
                    'tipo' => $validated['tipo'],
                    'duracion_segundos' => $validated['duracion_segundos'] ?? 0,
                    'xp_ganado' => $xp,
                    'notas' => $validated['nota_detectada'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            if (($validated['tipo'] ?? '') === 'afinacion' && Schema::hasTable('sesiones_afinacion')) {
                DB::table('sesiones_afinacion')->insert([
                    'id_estudiante' => $estudiante->id_estudiante,
                    'instrumento' => null,
                    'nota_objetivo' => $validated['nota_objetivo'] ?? null,
                    'nota_detectada' => $validated['nota_detectada'] ?? null,
                    'frecuencia' => $validated['frecuencia'] ?? null,
                    'desviacion_cents' => $validated['desviacion_cents'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            if (($validated['tipo'] ?? '') === 'ritmo' && Schema::hasTable('sesiones_ritmo')) {
                DB::table('sesiones_ritmo')->insert([
                    'id_estudiante' => $estudiante->id_estudiante,
                    'bpm' => $validated['bpm'] ?? 80,
                    'patron' => $validated['patron'] ?? '4/4',
                    'precision' => $validated['precision'] ?? null,
                    'xp_ganado' => $xp,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            if (! empty($validated['id_ejercicio']) && Schema::hasTable('progreso')) {
                DB::table('progreso')->insert([
                    'id_estudiante' => $estudiante->id_estudiante,
                    'id_ejercicio' => $validated['id_ejercicio'],
                    'precision' => $validated['precision'] ?? null,
                    'puntaje' => $xp,
                    'estado' => 'completado',
                    'fecha' => now(),
                ]);
            }

            $gamificationService->awardXp($estudiante->id_estudiante, $xp, 'Practica '.$validated['tipo']);
        });

        return response()->json([
            'message' => 'Practica registrada',
            'xp' => $xp,
        ]);
    }

    private function xpForPractice(array $payload): int
    {
        $base = match ($payload['tipo']) {
            'afinacion' => 15,
            'ritmo' => 20,
            'teoria' => 10,
            default => 12,
        };

        $durationBonus = min(20, intdiv((int) ($payload['duracion_segundos'] ?? 0), 60) * 2);
        $precisionBonus = isset($payload['precision']) ? (int) floor(((float) $payload['precision']) / 10) : 0;

        return max(5, $base + $durationBonus + $precisionBonus);
    }

    public function submitTask(Request $request, int $idTarea)
    {
        if (session('rol') !== 'estudiante') {
            return response()->json(['message' => 'Inicia sesion como estudiante para continuar'], 401);
        }

        $estudiante = DB::table('estudiantes')->where('id_usuario', session('usuario_id'))->first();

        if (! $estudiante) {
            return response()->json(['message' => 'No encontramos el perfil de estudiante'], 404);
        }

        $tarea = DB::table('tareas')
            ->where('id_tarea', $idTarea)
            ->where(function ($query) use ($estudiante) {
                $query->where('id_estudiante', $estudiante->id_estudiante)
                      ->orWhere('id_elenco', $estudiante->id_elenco);
            })
            ->first();

        if (! $tarea) {
            return response()->json(['message' => 'No se encontro la tarea o no tienes permiso para entregarla'], 404);
        }

        $validated = $request->validate([
            'comentario_estudiante' => ['nullable', 'string', 'max:1000'],
            'archivo' => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($idTarea, $estudiante, $validated, $tarea) {
            DB::table('entregas_tareas')->updateOrInsert(
                ['id_tarea' => $idTarea, 'id_estudiante' => $estudiante->id_estudiante],
                [
                    'comentario_estudiante' => $validated['comentario_estudiante'] ?? null,
                    'archivo' => $validated['archivo'] ?? null,
                    'estado' => 'entregada',
                    'entregado_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            // Si es tarea individual, marcamos la tarea como entregada también
            if ($tarea->id_estudiante) {
                DB::table('tareas')->where('id_tarea', $idTarea)->update(['estado' => 'entregada']);
            }

            if (Schema::hasTable('notificaciones')) {
                // Notificar al profesor
                $idUsuarioProfesor = DB::table('profesores')->where('id_profesor', $tarea->id_profesor)->value('id_usuario');
                if ($idUsuarioProfesor) {
                    $nombreAlumno = trim(session('nombre') . ' ' . (session('apellido_paterno') ?? ''));
                    DB::table('notificaciones')->insert([
                        'id_usuario' => $idUsuarioProfesor,
                        'titulo' => 'Nueva entrega de tarea',
                        'mensaje' => "El estudiante {$nombreAlumno} ha entregado la tarea: '{$tarea->titulo}'",
                        'tipo' => 'tarea',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        });

        return response()->json([
            'message' => 'Tarea entregada con exito',
        ]);
    }
}
