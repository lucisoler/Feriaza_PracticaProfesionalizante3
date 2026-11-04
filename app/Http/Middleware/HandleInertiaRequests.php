<?php

namespace App\Http\Middleware;

use App\Models\Aviso;
use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Datos que reciben todas las páginas en $page.props.
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),

            // Mensajes que mandan los controladores con ->with(...)
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
            ],

            // Lista de categorías para los selectores de los formularios
            'categorias' => fn () => Categoria::orderBy('prioridad')
                ->orderBy('nombre')
                ->get(['id', 'nombre', 'recibe_mesa', 'color']),

            // Avisos sin leer del panel del coordinador (ej.: un feriante cedió su lugar).
            // El frontend los pide de nuevo por polling, así que acá conviene que sea barato.
            'avisos' => fn () => Auth::check()
                ? Aviso::whereNull('leido_at')->latest()->limit(20)->get(['id', 'mensaje', 'created_at'])
                : [],
        ];
    }
}
