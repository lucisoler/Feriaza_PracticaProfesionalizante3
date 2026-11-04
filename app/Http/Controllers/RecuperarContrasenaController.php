<?php

namespace App\Http\Controllers;

use App\Models\Feriante;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Inertia\Inertia;
use Inertia\Response;

/**
 * "¿Olvidaste tu contraseña?" del feriante: recibe un enlace por correo y
 * con él crea una contraseña nueva (broker "feriantes" de config/auth.php).
 */
class RecuperarContrasenaController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Feriante/OlvideContrasena');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate(['email' => 'required|email']);

        $estado = Password::broker('feriantes')->sendResetLink($request->only('email'));

        if ($estado === Password::RESET_THROTTLED) {
            return back()->withErrors(['email' => 'Ya te mandamos un enlace hace poco. Esperá un minuto antes de pedir otro.']);
        }

        // El mismo mensaje exista o no la cuenta, para no revelar qué emails están registrados
        return back()->with('success', 'Si ese email tiene una cuenta, te mandamos un enlace para crear una contraseña nueva. Revisá tu correo (también la carpeta de spam).');
    }

    public function edit(Request $request, string $token): Response
    {
        return Inertia::render('Feriante/RestablecerContrasena', [
            'token' => $token,
            'email' => $request->query('email', ''),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $estado = Password::broker('feriantes')->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (Feriante $feriante, string $password) {
                $feriante->forceFill(['password' => $password])->save();

                event(new PasswordReset($feriante));
            }
        );

        if ($estado !== Password::PASSWORD_RESET) {
            return back()->withErrors(['email' => 'El enlace no es válido o ya venció. Pedí uno nuevo.']);
        }

        return redirect('/mi-cuenta/login')->with('success', 'Listo, ya podés entrar con tu contraseña nueva.');
    }
}
