<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateSession
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->session()->has('usuario_id') || ! $request->session()->has('rol')) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'No autenticado.',
                ], 401);
            }

            return redirect('/login')->with('error', 'Inicia sesion para continuar');
        }

        return $next($request);
    }
}
