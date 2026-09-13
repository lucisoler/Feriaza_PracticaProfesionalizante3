<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Feriante;
use App\Models\Puesto;
use Illuminate\Validation\Rule;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;



class CoordinadorFerianteController extends Controller
{

    public function create():Response{

      return Inertia:: render('Coordonador/FerianteForm',[
        'feriante'=>null,
        'puestos'=>Puesto::orderBy('numero')->get(),

    ]);

    }

    public function store(Request $request):RedirectResponse{
        $data = $this->validarDatos($request,null);

        $puestoId=$data['puesto_id'] ?? null;
        unset ($data['puesto_id']);

        $feriante = Feriante::create($data);

        if ($puestoId){
            Puesto::findOrfail($puestoId)->update([
             'estado'=>'reservado',
             'feriante_id'=>$feriante->id
            ]);
        }

        return redirect('/coordinador')->with('success','Feriante creado.');
    }

    public function edit(int $id):Response
    {

    $feriante=Feriante::with('puesto')->findOrFail($id);

    return Inertia::render('Coordinador/FerianteForm',[
        'feriante'=>$feriante,
        'puestos'=>Puesto::orderBy('numero')->get(),
    ]);

}

public function update(Request $request, int $id): RedirectResponse
{
$feriante= Feriante::with('puesto')->findOrFail($id);
$data= $this->validarDatos($request,$feriante->id);

if (empty($data['password'])){
    unset($data['password']);
}

$puestoId=$data['puesto_id']?? null;
unset($data['puesto_id']);

$feriante->update($data);

$puestoActualID=$feriante->puesto->id ?? null;


if ($puestoId !==$puestoActualID){
    if($feriante->puesto){
        $feriante->puesto->update(['estado'=>'libre','feriante_id'=>null]);
    }

if ($puestoId){
    Puesto::findOrfail($puestoId)->update([
        'estado'=>'reservado',
        'feriante_id'=>$feriante->id,
    ]);
}
   }

   return redirect('/coordinador')->with('success','Feriante actualizado.');

}
public function cambiarEstado(Request $request,int $id):RedirectResponse
{

$data=$request->validate([
    'estado'=>'required|in:pendiente,aprovado,rechazado',
]);

Feriante::findOrFail($id)->update($data);

return back();

}



public function destroy(int $id):RedirectResponse
{

$feriante =Feriante::with('puesto')->findOrFail($id);

if($feriante->puesto){
    $feriante->puesto->update(['estado'=>'libre','feriante_id'=>null]);

}

$feriante->delete();

return  back()->with('success','Feriante eliminado.');

}



private function validarDatos(Request $request,?int $idFeriante): array
{

return $request->validate([
    'nombre'=>'required|string|max:100',
    'apellido'=>'required|string|max:100',
    'email'=>[
        'required','email',
        Rule::Unique('feriantes','email')->ignore($idFeriante),

    ],
'password'=>$idFeriante ? 'nullable|string|min:6': 'required|string|min:6',
'nombre_emprendimiento'=>'nullable|string|max:150',
'rubro'=>'required|in:artesano,masas,manualidades,revendedor,otro',
'rubro_otro'=>'nullable|required_if:rubro,otro|string|max:100',
'telefono'=>'required|string|max:20',
'instagram'=>'nullable|string|max:100',
'facebook'=>'nullable|string|max:100',
'tiktok'=>'nullable|string|max100',
'consulta'=>'nullable|string',
'estado'=>'nullable|in:pendiente,aprovado,rechazado',
'asistencia_confirmada'=>'nullable|boolean',
'puesto_id'=>'nullable|exists:puestos,id,'

]);




}

}
