<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CoordinadorController extends Controller
{
    public function index():response {
        $feriantes=Feriante::with('puesto')->latest()->get();
        return Inertia::render('Coordinador',[
        'feriantes'=>$feriantes,
        'whatsapp'=>config('feria.whatsapp_coordinador')
        ]);
    }

public function login(Request $request):RedirectResponse{
$creds = $request->validate([
    'email'=>'required|email',
    'password'=>'required',
]);
if(!Auth::attempt($creds)){
return back()->withErrors(['email'=>'Credenciales Incorrectas']);
}
return redirect('/coordinador');
}
}
