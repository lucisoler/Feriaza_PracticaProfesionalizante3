<?php

namespace App\Http\Middleware;

use App\Models\Categoria;
use Illuminate\Http\Request;
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
                'whatsapp' => fn () => $request->session()->get('whatsapp'),
            ],

            // Lista de categorías para los selectores de los formularios
            'categorias' => fn () => Categoria::orderBy('prioridad')
                ->orderBy('nombre')
                ->get(['id', 'nombre', 'recibe_mesa', 'color']),
        ];
    }
}
