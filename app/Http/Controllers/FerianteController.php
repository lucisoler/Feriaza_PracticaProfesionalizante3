<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Feriante;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class FerianteController extends Controller
{
    // Formulario de registro con email y contraseña (RF-01)
    public function create(): Response|RedirectResponse
    {
        if (Auth::guard('feriante')->check()) {
            return redirect('/mi-cuenta');
        }

        return Inertia::render('Feriante/Registro', ['social' => null]);
    }

    // CREATE público: crea la cuenta (POST /feriantes). La mesa se elige después, por evento (RF-07)
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => 'required|email|unique:feriantes,email',
            'password' => 'required|string|min:8|confirmed',
            ...self::reglasDelPerfil(),
        ]);

        $feriante = Feriante::create($data);

        // Queda con la sesión iniciada para seguir directo con la inscripción a un evento
        Auth::guard('feriante')->login($feriante);
        $request->session()->regenerate();

        return redirect('/mi-cuenta/inscripciones/nueva')
            ->with('success', '¡Tu cuenta quedó creada! Ahora elegí el evento, el día y tu mesa.');
    }

    /** Datos del feriante y su emprendimiento, compartidos por el registro con email y el social. */
    public static function reglasDelPerfil(): array
    {
        return [
            'nombre' => 'required|string|max:100',
            'apellido' => 'required|string|max:100',
            'nombre_emprendimiento' => 'nullable|string|max:150',
            'categoria_id' => 'required|exists:categorias,id',
            'rubro_otro' => 'nullable|required_if:categoria_id,' . Categoria::idOtro() . '|string|max:100',
            'telefono' => 'required|string|max:20',
            'instagram' => 'nullable|string|max:100',
            'facebook' => 'nullable|string|max:100',
            'tiktok' => 'nullable|string|max:100',
            'consulta' => 'nullable|string',
        ];
    }
}
