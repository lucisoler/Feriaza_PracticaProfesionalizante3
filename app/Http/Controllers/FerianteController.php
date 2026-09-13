<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\PuestoController;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Models\Puesto;
use App\Models\Feriante;


class FerianteController extends Controller
{


    public function store(Request $request)
    {
        $data= $request->validate([
            'nombre'=>'required|string|max:100',
            'apellido'=>'required|string|max:100',
            'nombre_emprendimiento'=>'nullable|sring|max:150',
            'rubro'=>'required|in:artesano,masas,manualidades,revendedor,otro',
            'rubro_otro'=> 'nullable|required_if:rubro,otro|string|max:100',
            'telefono'=>'required|string|max:20',
            'instagram'=>'nullable|string|',
            'facebook'=>'nullable|string|',
            'tik tok'=>'nullable|string|',
            'consulta'=>'nullable|string',
            'puesto_id'=>'required|exists:puestos,id',
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
    . "Rubro:{$feriante->rubro}\n"
    . "puesto N:{$puesto->numero}\n"
    . "Tel:{$feriante->telefono}"
    );

    $link= "Https:/wa.me/{$numero}?text={$msg}";


    return Inertia::location('/gracias');

    }




}
