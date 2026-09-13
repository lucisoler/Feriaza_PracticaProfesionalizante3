<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class MiCuentaController extends Controller
{
    public function show():Response
    {
        $feriante=Auth::guard('feriante')->user()->load('puesto');


        return Inertia::render('Feriante/MiCuenta',[
            'feriante'=>$feriante,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $feriante =Auth::guard('feriante')->user();


        $data =$request->validate([
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

        if (empty($data['pasword'])){
            unset($data['pasword']);
        }

        $feriante->update($data);

        return back()->with('sucess','tus datos fueron actualizados.');
    }

    public function destroy(Request $request):RedirectResponse
    {
        $feriante=Auth::guard('feriante')->user();

        if($feriante->puesto){
            $feriante->puesto->update([
                'estado'=>'libre',
                'feriante_id'=>null,
            ]);
        }

        Auth::guard('feriante')->logout();
             $feriante->delete();
             $request->session()->invalidate();

        return redirect('/')->with('success','Tu inscripción fue cancelada.');



    }

}
