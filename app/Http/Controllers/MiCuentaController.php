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

        // Inscripciones por evento y día, de la más próxima a la más lejana
        $inscripciones = $feriante->inscripciones()
            ->with(['evento:id,nombre,lugar_id,hora_inicio,pago_fecha,pago_hora,estado', 'evento.lugar:id,nombre', 'puesto:id,numero'])
            ->orderBy('fecha')
            ->get()
            ->map(fn ($i) => [
                'id' => $i->id,
                'fecha' => $i->fecha->toDateString(),
                'estado' => $i->estado,
                'evento' => $i->evento->nombre,
                'evento_estado' => $i->evento->estado,
                'lugar' => $i->evento->lugar?->nombre,
                'hora' => substr($i->evento->hora_inicio, 0, 5),
                'mesa' => $i->puesto?->numero,
                // Reconfirmación 72 horas antes y pago de la mesa (RN-16, RN-17)
                'confirmacion' => $i->confirmacion,
                'puede_reconfirmar' => $i->puedeReconfirmar(),
                'reconfirmacion_desde' => $i->aperturaReconfirmacion()->format('Y-m-d H:i'),
                'vencida' => $i->inicio()->isPast(),
                'medio_pago' => $i->medio_pago,
                'pagado' => $i->pagado_at !== null,
                'pago_fecha' => $i->evento->pago_fecha?->toDateString(),
                'pago_hora' => $i->evento->pago_hora ? substr($i->evento->pago_hora, 0, 5) : null,
            ]);

        return Inertia::render('Feriante/MiCuenta',[
            'feriante'=>$feriante,
            'inscripciones'=>$inscripciones,
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
