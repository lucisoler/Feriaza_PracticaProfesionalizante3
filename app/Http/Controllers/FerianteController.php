<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\PuestoController;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Models\Puesto;
use App\Models\Feriante;
use App\Models\Categoria;

class FerianteController extends Controller
{


    public function store(Request $request)
    {
                $data = $request->validate([
            'nombre' => 'required|string|max:100',
            'apellido' => 'required|string|max:100',
            'email' => 'required|email|unique:feriantes,email',
            'password' => 'required|string|min:8|confirmed',
            'nombre_emprendimiento' => 'nullable|string|max:150',
            'categoria_id' => 'required|exists:categorias,id',
            'rubro_otro' => 'nullable|required_if:categoria_id,' . Categoria::idOtro() . '|string|max:100',
            'telefono' => 'required|string|max:20',
            'instagram' => 'nullable|string|max:100',
            'facebook' => 'nullable|string|max:100',
            'tiktok' => 'nullable|string|max:100',
            'consulta' => 'nullable|string',
            'asistencia_confirmada' => 'required|accepted',
            'puesto_id' => 'required|exists:puestos,id',
        ]);



        $puesto = Puesto::findOrFail($data['puesto_id']);

        if($puesto->estado === 'reservado'){
    return back()->withErrors(['puesto_id'=>'Ese puesto ya fué reservado.Elegí Otro']);
    }

    unset($data['puesto_id']);
      $feriante=Feriante::create($data);

 $puesto->update(['estado' =>'reservado','feriante_id'=>$feriante->id]);

 Auth::guard('feriante')->login($feriante);

 $numero = env('WHATSAPP_COORDINADOR');
 $msg=urlencode(
    "nuevo feriante:\n"
    . "{$feriante->nombre} {$feriante->apellido}\n"
    . "Categoría:{$feriante->categoria->nombre}\n"
    . "puesto N:{$puesto->numero}\n"
    . "Tel:{$feriante->telefono}"
    );

    $link= "https:/wa.me/{$numero}?text={$msg}";


    return Inertia::location('/gracias');

    }




}
