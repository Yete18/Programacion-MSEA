<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateStudentProfileRequest;
use App\Services\StudentDashboardService;

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
}
