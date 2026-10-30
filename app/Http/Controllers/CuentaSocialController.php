<?php

namespace App\Http\Controllers;

use App\Models\Feriante;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

/**
 * Crear cuenta o entrar con Google o Facebook (RF-01, RF-03).
 *
 * Si la cuenta ya existe (mismo proveedor o mismo email) se inicia sesión.
 * Si no, los datos del proveedor quedan en la sesión y el feriante completa
 * lo que falta (teléfono y categoría) antes de crear la cuenta.
 */
class CuentaSocialController extends Controller
{
    private const CLAVE_SESION = 'registro_social';

    public function redirect(string $proveedor): RedirectResponse
    {
        if (! config("services.{$proveedor}.client_id")) {
            return redirect('/')->withErrors([
                'social' => 'El acceso con ' . Str::ucfirst($proveedor) . ' todavía no está configurado. Usá tu email y contraseña.',
            ]);
        }

        return Socialite::driver($proveedor)->redirect();
    }

    public function callback(Request $request, string $proveedor): RedirectResponse
    {
        try {
            $usuario = Socialite::driver($proveedor)->user();
        } catch (Throwable) {
            return redirect('/')->withErrors([
                'social' => 'No pudimos conectar con ' . Str::ucfirst($proveedor) . '. Probá de nuevo.',
            ]);
        }

        $email = $usuario->getEmail();

        $feriante = Feriante::where('proveedor', $proveedor)->where('proveedor_id', $usuario->getId())->first()
            ?? ($email ? Feriante::where('email', $email)->first() : null);

        if ($feriante) {
            // Una cuenta creada con email queda vinculada al proveedor para la próxima vez
            if (! $feriante->proveedor) {
                $feriante->update(['proveedor' => $proveedor, 'proveedor_id' => $usuario->getId()]);
            }

            Auth::guard('feriante')->login($feriante);
            $request->session()->regenerate();

            return redirect('/mi-cuenta');
        }

        [$nombre, $apellido] = $this->separarNombre($usuario);

        $request->session()->put(self::CLAVE_SESION, [
            'proveedor' => $proveedor,
            'proveedor_id' => (string) $usuario->getId(),
            'nombre' => $nombre,
            'apellido' => $apellido,
            'email' => $email,
        ]);

        return redirect('/registro/social');
    }

    // Formulario para completar los datos que el proveedor no da
    public function create(Request $request): Response|RedirectResponse
    {
        $social = $request->session()->get(self::CLAVE_SESION);

        if (! $social) {
            return redirect('/');
        }

        return Inertia::render('Feriante/Registro', [
            'social' => [
                'proveedor' => $social['proveedor'],
                'nombre' => $social['nombre'],
                'apellido' => $social['apellido'],
                'email' => $social['email'],
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $social = $request->session()->get(self::CLAVE_SESION);

        if (! $social) {
            return redirect('/');
        }

        $data = $request->validate([
            // Si el proveedor no compartió el email, lo escribe el feriante
            'email' => $social['email'] ? 'nullable' : 'required|email|unique:feriantes,email',
            ...FerianteController::reglasDelPerfil(),
        ]);

        $feriante = Feriante::create([
            ...$data,
            'email' => $social['email'] ?? $data['email'],
            'proveedor' => $social['proveedor'],
            'proveedor_id' => $social['proveedor_id'],
        ]);

        $request->session()->forget(self::CLAVE_SESION);
        Auth::guard('feriante')->login($feriante);
        $request->session()->regenerate();

        return redirect('/mi-cuenta/inscripciones/nueva')
            ->with('success', '¡Tu cuenta quedó creada! Ahora elegí el evento, el día y tu mesa.');
    }

    /** @return array{0: string, 1: string} */
    private function separarNombre($usuario): array
    {
        // Google manda nombre y apellido por separado en "raw"; Facebook, solo el nombre completo
        $raw = $usuario->getRaw();
        if (! empty($raw['given_name'])) {
            return [$raw['given_name'], $raw['family_name'] ?? ''];
        }

        $partes = explode(' ', trim((string) $usuario->getName()), 2);

        return [$partes[0] ?? '', $partes[1] ?? ''];
    }
}
