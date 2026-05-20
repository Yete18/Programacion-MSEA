<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class PasswordResetController extends Controller
{
    public function showRequestForm()
    {
        return view('forgot-password');
    }

    public function sendCode(Request $request)
    {
        $validated = $request->validate([
            'correo' => 'required|email',
        ]);

        $correo = strtolower($validated['correo']);
        $usuario = DB::table('usuarios')->where('correo', $correo)->first();

        $request->session()->put('password_reset_correo', $correo);
        $request->session()->forget(['password_reset_verified', 'password_reset_code_id']);

        if ($usuario) {
            $codigo = (string) random_int(100000, 999999);

            DB::table('password_reset_codes')
                ->where('correo', $correo)
                ->whereNull('used_at')
                ->update(['used_at' => now()]);

            DB::table('password_reset_codes')->insert([
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

        return redirect('/verify-code')
            ->with('success', 'Si el correo existe, enviamos un codigo de verificacion.');
    }

    public function showCodeForm(Request $request)
    {
        if (! $request->session()->has('password_reset_correo')) {
            return redirect('/forgot-password');
        }

        return view('verify-code', [
            'correo' => $request->session()->get('password_reset_correo'),
        ]);
    }

    public function verifyCode(Request $request)
    {
        $validated = $request->validate([
            'codigo' => 'required|digits:6',
        ]);

        $correo = $request->session()->get('password_reset_correo');

        if (! $correo) {
            return redirect('/forgot-password');
        }

        $codigos = DB::table('password_reset_codes')
            ->where('correo', $correo)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->orderByDesc('created_at')
            ->get();

        foreach ($codigos as $codigoGuardado) {
            if (Hash::check($validated['codigo'], $codigoGuardado->codigo)) {
                $request->session()->put('password_reset_verified', true);
                $request->session()->put('password_reset_code_id', $codigoGuardado->id);

                return redirect('/reset-password');
            }
        }

        return back()->with('error', 'El codigo no es valido o ya expiro.');
    }

    public function showResetForm(Request $request)
    {
        if (! $request->session()->get('password_reset_verified')) {
            return redirect('/forgot-password');
        }

        return view('reset-password');
    }

    public function resetPassword(Request $request)
    {
        if (! $request->session()->get('password_reset_verified')) {
            return redirect('/forgot-password');
        }

        $validated = $request->validate([
            'contrasena' => 'required|min:6|confirmed',
        ]);

        $correo = $request->session()->get('password_reset_correo');
        $codigoId = $request->session()->get('password_reset_code_id');

        DB::transaction(function () use ($correo, $codigoId, $validated) {
            DB::table('usuarios')
                ->where('correo', $correo)
                ->update([
                    'contrasena' => Hash::make($validated['contrasena']),
                ]);

            DB::table('password_reset_codes')
                ->where('id', $codigoId)
                ->update(['used_at' => now()]);
        });

        $request->session()->forget([
            'password_reset_correo',
            'password_reset_verified',
            'password_reset_code_id',
        ]);

        return redirect('/login')->with('success', 'Contrasena actualizada. Ya puedes iniciar sesion.');
    }
}
