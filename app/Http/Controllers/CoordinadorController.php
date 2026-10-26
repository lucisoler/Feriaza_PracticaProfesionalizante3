<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\Feriante;

class CoordinadorController extends Controller
{
    public function index():Response {
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

public function logout(Request $request):RedirectResponse{
Auth::logout();
$request->session()->invalidate();
$request->session()->regenerateToken();

return redirect('/login');
}
}
