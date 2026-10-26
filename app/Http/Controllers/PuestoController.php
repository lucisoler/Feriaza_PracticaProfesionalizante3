<?php

namespace App\Http\Controllers;

use App\Models\Puesto;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class PuestoController extends Controller
{
    public function index(): Response
    {
        $puestos = Puesto::with('feriante:id,nombre,apellido')->orderBy('numero')->get();

        return Inertia::render('Formulario', ['puestos' => $puestos]);
    }

    public function liberar(int $id): RedirectResponse
    {
        $puesto = Puesto::findOrFail($id);
        $puesto->update(['estado' => 'libre', 'feriante_id' => null]);

        return back();
    }
}
