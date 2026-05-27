<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Services\AuthService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function login(LoginRequest $request, AuthService $authService)
    {
        $validated = $request->validated();

        $rolSolicitado = $validated['rol'] === 'admin' ? 'director' : $validated['rol'];
        $usuario = $authService->findUsuarioConRol($validated['correo']);

        if (! $usuario) {
            return back()
                ->withInput($request->only('correo', 'rol'))
                ->with('error', 'Correo no encontrado');
        }

        if (! $authService->contrasenaValida($validated['contrasena'], $usuario)) {
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

    public function register(RegisterRequest $request, AuthService $authService)
    {
        $validated = $request->validated();
        $idUsuario = $authService->registrarEstudiante($validated);

        if (! $idUsuario) {
            return back()->withInput()->with('error', 'Rol no valido');
        }

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
}
