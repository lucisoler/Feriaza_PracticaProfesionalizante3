<?php

use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Inertia en todas las páginas web
        $middleware->web(append: [
            HandleInertiaRequests::class,
        ]);

        // Habilidades de los tokens de Sanctum: 'feriante' o 'coordinador'
        $middleware->alias([
            'abilities' => \Laravel\Sanctum\Http\Middleware\CheckAbilities::class,
            'ability' => \Laravel\Sanctum\Http\Middleware\CheckForAnyAbility::class,
        ]);

        // Si alguien sin sesión entra a una página privada, va al login que corresponde
        $middleware->redirectGuestsTo(fn (Request $request) =>
            $request->is('mi-cuenta*') ? '/mi-cuenta/login' : '/login'
        );
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // La API siempre responde en JSON (401, 403, 422...), nunca con una página HTML
        $exceptions->shouldRenderJsonWhen(fn (Request $request) => $request->is('api/*'));
    })->create();
