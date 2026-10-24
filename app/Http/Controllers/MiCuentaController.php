<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use App\Models\Categoria;
use App\Models\Feriante;

class MiCuentaController extends Controller
{
    public function show():Response
    {
        $feriante = Auth::guard('feriante')->user();

        if (! $feriante instanceof Feriante) {
            abort(401);
        }

        $feriante->load('puesto');

        return Inertia::render('Feriante/MiCuenta',[
            'feriante'=>$feriante,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $feriante = Auth::guard('feriante')->user();

        if (! $feriante instanceof Feriante) {
            abort(401);
        }

        $data =$request->validate([
         'nombre'=>'required|string|max:100',
            'apellido'=>'required|string|max:100',
            'nombre_emprendimiento'=>'nullable|string|max:150',
            'categoria_id' => 'required|exists:categorias,id',
            'rubro_otro' => 'nullable|required_if:categoria_id,' . Categoria::idOtro() . '|string|max:100',
            'telefono'=>'required|string|max:20',
            'instagram'=>'nullable|string|',
            'facebook'=>'nullable|string|',
            'tiktok'=>'nullable|string|',
            'consulta'=>'nullable|string|',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        if (empty($data['password'])){
            unset($data['password']);
        }

                $feriante->update($data);

        return back()->with('success', 'Tus datos fueron actualizados.');

    }

    public function destroy(Request $request):RedirectResponse
    {
        $feriante = Auth::guard('feriante')->user();

        if (! $feriante instanceof Feriante) {
            abort(401);
        }

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
