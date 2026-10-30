<?php

namespace App\Http\Controllers;

use App\Models\Puesto;
use Illuminate\Http\RedirectResponse;

class PuestoController extends Controller
{
    public function liberar(int $id): RedirectResponse
    {
        $puesto = Puesto::findOrFail($id);
        $puesto->update(['estado' => 'libre', 'feriante_id' => null]);

        return back();
    }
}
