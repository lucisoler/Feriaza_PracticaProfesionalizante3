<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Response;

class PuestoController extends Controller
{
public function index():Response{

$puestos=Puesto::with('feriante:id,nombre,apellido,rubro')->orderBy('número')->get();
return Inertia::render('Formulario',['puestos'=>$puestos]);
}

public function liberar(int $id):RedirectResponse{
$puesto =Puesto::findOrFail($id);
$puesto->update(['estado'=>'libre','feriante_id'=>null]);
return back();
}

}
