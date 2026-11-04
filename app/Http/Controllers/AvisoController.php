<?php

namespace App\Http\Controllers;

use App\Models\Aviso;
use Illuminate\Http\RedirectResponse;

class AvisoController extends Controller
{
    public function marcarLeido(Aviso $aviso): RedirectResponse
    {
        $aviso->update(['leido_at' => now()]);

        return back();
    }
}
