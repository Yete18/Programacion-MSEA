<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MobileApiToken;
use App\Models\Usuario;
use App\Services\AuthService;
use App\Services\GamificationService;
use App\Services\StudentDashboardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MobileController extends Controller
{
    public function login(Request $request, AuthService $authService)
    {
        $validated = $request->validate([
            'correo' => ['required', 'email'],
            'contrasena' => ['required', 'string'],
            'rol' => ['required', 'in:director,admin,profesor,estudiante,padre'],
            'device_name' => ['nullable', 'string', 'max:120'],
        ]);

        $rolSolicitado = $validated['rol'] === 'admin' ? 'director' : $validated['rol'];
        $usuario = $authService->findUsuarioConRol($validated['correo']);

        if (! $usuario || ! $authService->contrasenaValida($validated['contrasena'], $usuario)) {
            return response()->json(['message' => 'Credenciales invalidas'], 422);
        }

        if ($usuario->rol_nombre !== $rolSolicitado) {
            return response()->json(['message' => 'El rol seleccionado no corresponde a este usuario'], 422);
        }

        $plainToken = Str::random(64);
        MobileApiToken::query()->create([
            'id_usuario' => $usuario->id_usuario,
            'token_hash' => hash('sha256', $plainToken),
            'device_name' => $validated['device_name'] ?? 'Flutter',
            'last_used_at' => now(),
        ]);

        return response()->json([
            'token' => $plainToken,
            'usuario' => $this->usuarioPayload($usuario),
        ]);
    }

    public function me(Request $request, StudentDashboardService $studentDashboardService)
    {
        $usuario = $this->authenticatedUser($request);

        if (! $usuario) {
            return response()->json(['message' => 'No autenticado'], 401);
        }

        $payload = [
            'usuario' => $this->usuarioPayload($usuario),
            'dashboard' => null,
        ];

        if ($usuario->rol?->nombre === 'estudiante') {
            $payload['dashboard'] = $studentDashboardService->dashboardPayload($usuario->id_usuario);
        }

        if ($usuario->rol?->nombre === 'profesor') {
            $payload['dashboard'] = $this->profesorPayload($usuario->id_usuario);
        }

        if ($usuario->rol?->nombre === 'padre') {
            $payload['dashboard'] = $this->padrePayload($usuario->id_usuario);
        }

        if ($usuario->rol?->nombre === 'director') {
            $payload['dashboard'] = [
                'totalProfesores' => DB::table('profesores')->count(),
                'totalEstudiantes' => DB::table('estudiantes')->count(),
                'totalTareas' => DB::table('tareas')->count(),
                'totalPracticas' => DB::table('practicas_autonomas')->count(),
            ];
        }

        return response()->json($payload);
    }

    public function practice(Request $request, GamificationService $gamificationService)
    {
        $usuario = $this->authenticatedUser($request);

        if (! $usuario || $usuario->rol?->nombre !== 'estudiante') {
            return response()->json(['message' => 'Solo estudiantes pueden registrar practica'], 403);
        }

        $validated = $request->validate([
            'tipo' => ['required', 'string', 'max:40'],
            'duracion_segundos' => ['nullable', 'integer', 'min:0', 'max:7200'],
            'precision' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        $estudiante = DB::table('estudiantes')->where('id_usuario', $usuario->id_usuario)->first();

        if (! $estudiante) {
            return response()->json(['message' => 'Perfil de estudiante no encontrado'], 404);
        }

        $xp = match ($validated['tipo']) {
            'ritmo' => 24,
            'afinacion' => 18,
            'teoria' => 15,
            default => 12,
        };

        DB::table('practicas_autonomas')->insert([
            'id_estudiante' => $estudiante->id_estudiante,
            'tipo' => $validated['tipo'],
            'duracion_segundos' => $validated['duracion_segundos'] ?? 0,
            'xp_ganado' => $xp,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $gamificationService->awardXp($estudiante->id_estudiante, $xp, 'Practica movil '.$validated['tipo']);

        return response()->json(['message' => 'Practica registrada', 'xp' => $xp]);
    }

    public function logout(Request $request)
    {
        $token = $this->tokenFromRequest($request);

        if ($token) {
            MobileApiToken::query()->where('token_hash', hash('sha256', $token))->delete();
        }

        return response()->json(['message' => 'Sesion movil cerrada']);
    }

    private function authenticatedUser(Request $request): ?Usuario
    {
        $token = $this->tokenFromRequest($request);

        if (! $token) {
            return null;
        }

        $record = MobileApiToken::query()
            ->where('token_hash', hash('sha256', $token))
            ->first();

        if (! $record) {
            return null;
        }

        $record->update(['last_used_at' => now()]);

        return Usuario::query()->with('rol')->find($record->id_usuario);
    }

    private function tokenFromRequest(Request $request): ?string
    {
        $header = (string) $request->header('Authorization');

        if (str_starts_with($header, 'Bearer ')) {
            return substr($header, 7);
        }

        return null;
    }

    private function usuarioPayload(Usuario $usuario): array
    {
        return [
            'id' => $usuario->id_usuario,
            'nombre' => trim($usuario->nombres.' '.$usuario->apellido_paterno),
            'correo' => $usuario->correo,
            'rol' => $usuario->rol?->nombre,
        ];
    }

    private function profesorPayload(int $idUsuario): array
    {
        $profesor = DB::table('profesores')->where('id_usuario', $idUsuario)->first();

        return [
            'totalAlumnos' => $profesor ? DB::table('estudiantes')->where('id_profesor', $profesor->id_profesor)->count() : 0,
            'tareas' => $profesor ? DB::table('tareas')->where('id_profesor', $profesor->id_profesor)->count() : 0,
            'entregasPendientes' => $profesor
                ? DB::table('entregas_tareas as et')->join('tareas as t', 't.id_tarea', '=', 'et.id_tarea')->where('t.id_profesor', $profesor->id_profesor)->where('et.estado', 'entregada')->count()
                : 0,
        ];
    }

    private function padrePayload(int $idUsuario): array
    {
        $padre = DB::table('padres')->where('id_usuario', $idUsuario)->first();

        if (! $padre) {
            return ['estudiantes' => []];
        }

        $estudiantes = DB::table('estudiante_padre as ep')
            ->join('estudiantes as e', 'e.id_estudiante', '=', 'ep.id_estudiante')
            ->join('usuarios as u', 'u.id_usuario', '=', 'e.id_usuario')
            ->leftJoin('gamificacion_perfiles as g', 'g.id_estudiante', '=', 'e.id_estudiante')
            ->where('ep.id_padre', $padre->id_padre)
            ->select(['e.id_estudiante', 'u.nombres', 'u.apellido_paterno', DB::raw('coalesce(g.xp_total, 0) as xp'), DB::raw('coalesce(g.nivel, 1) as nivel')])
            ->get();

        return ['estudiantes' => $estudiantes];
    }
}
