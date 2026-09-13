<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class FerianteAuthController extends Controller
{
    public function showlogin(): Response
    {
           return Inertia::reader('Feriante/Login');
    }

    public function login(Request $request):RedirectResponse
    {
        $creds = $request->validate([
            'email'=>'required|email',
            'password'=>'required',
        ]);

        if(!Auth::guard('feriante')->attempt($creds)){
            return back()->withErrors([
                'email'=>'email o contraseña incorrectos.',
            ]);
        }

        $request->session()->regenerate();
        return redirect('/mi-cuenta');
    }

    public function logout(Request $request):RedirectResponse
    {
        Auth::guard('feriante')->logout();
        $request->session()->invalidate();

        return redirect('/');
    }
}

