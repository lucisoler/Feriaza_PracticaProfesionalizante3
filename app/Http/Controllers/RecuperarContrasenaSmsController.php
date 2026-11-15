<?php

namespace App\Http\Controllers;

use App\Models\Feriante;
use App\Services\SmsService;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

/**
 * Recuperación de contraseña del feriante por SMS: manda un código de 6 números
 * al teléfono cargado en la cuenta y, con ese código, crea una contraseña nueva.
 */
class RecuperarContrasenaSmsController extends Controller
{
    private const MINUTOS_VENCIMIENTO = 10;

    private const MAXIMO_INTENTOS = 5;

    public function store(Request $request, SmsService $smsService): RedirectResponse
    {
        $datos = $request->validate(['email' => 'required|email']);

        $feriante = Feriante::where('email', $datos['email'])->first();

        if ($feriante && $feriante->telefono) {
            $codigo = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

            Cache::put(self::claveDelCodigo($feriante->email), [
                'hash' => Hash::make($codigo),
                'intentos' => 0,
            ], now()->addMinutes(self::MINUTOS_VENCIMIENTO));

            try {
                $smsService->enviar(
                    $feriante->telefono,
                    "Feriaza: tu código para crear una contraseña nueva es {$codigo}. Vence en ".self::MINUTOS_VENCIMIENTO.' minutos.',
                );
            } catch (Throwable $e) {
                report($e);
                Cache::forget(self::claveDelCodigo($feriante->email));

                return back()->withErrors(['email' => 'No pudimos mandar el SMS. Probá de nuevo o recuperá tu contraseña por email.']);
            }
        }

        // El mismo mensaje exista o no la cuenta, para no revelar qué emails están registrados
        return redirect('/mi-cuenta/restablecer-sms?email='.urlencode($datos['email']))
            ->with('success', 'Si esa cuenta tiene un teléfono cargado, te mandamos un código de 6 números por SMS.');
    }

    public function create(Request $request): Response
    {
        return Inertia::render('Feriante/RestablecerSms', [
            'email' => (string) $request->query('email', ''),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'email' => 'required|email',
            'codigo' => 'required|digits:6',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $clave = self::claveDelCodigo($datos['email']);
        $pedido = Cache::get($clave);
        $feriante = Feriante::where('email', $datos['email'])->first();

        if (! $feriante || ! $pedido || ! Hash::check($datos['codigo'], $pedido['hash'])) {
            $this->registrarIntentoFallido($clave, $pedido);

            return back()->withErrors(['codigo' => 'El código no es válido o ya venció. Pedí uno nuevo.']);
        }

        $feriante->forceFill(['password' => $datos['password']])->save();
        Cache::forget($clave);
        event(new PasswordReset($feriante));

        return redirect('/mi-cuenta/login')->with('success', 'Listo, ya podés entrar con tu contraseña nueva.');
    }

    /**
     * @param  array{hash: string, intentos: int}|null  $pedido
     */
    private function registrarIntentoFallido(string $clave, ?array $pedido): void
    {
        if (! $pedido) {
            return;
        }

        $pedido['intentos']++;

        if ($pedido['intentos'] >= self::MAXIMO_INTENTOS) {
            Cache::forget($clave);

            return;
        }

        Cache::put($clave, $pedido, now()->addMinutes(self::MINUTOS_VENCIMIENTO));
    }

    private static function claveDelCodigo(string $email): string
    {
        return 'recuperacion-sms:'.sha1(strtolower($email));
    }
}
