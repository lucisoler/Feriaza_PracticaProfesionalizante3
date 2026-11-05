<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Feriante;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class MiCuentaController extends Controller
{
    public function show(): Response
    {
        $feriante = Auth::guard('feriante')->user();

        if (! $feriante instanceof Feriante) {
            abort(401);
        }

        // Inscripciones por evento y día, de la más próxima a la más lejana
        $inscripciones = $feriante->inscripciones()
            ->with(['evento:id,nombre,lugar_id,hora_inicio,estado', 'evento.lugar:id,nombre', 'puesto:id,numero'])
            ->orderBy('fecha')
            ->get()
            ->map(fn ($i) => [
                'id' => $i->id,
                'fecha' => $i->fecha->toDateString(),
                'es_hoy' => $i->fecha->isToday(),
                'estado' => $i->estado,
                'evento' => $i->evento->nombre,
                'evento_estado' => $i->evento->estado,
                'lugar' => $i->evento->lugar?->nombre,
                'hora' => substr($i->evento->hora_inicio, 0, 5),
                'mesa' => $i->puesto?->numero,
                // Reconfirmación 72 horas antes del día que le toca (RN-16)
                'confirmacion' => $i->confirmacion,
                'puede_reconfirmar' => $i->puedeReconfirmar(),
                'reconfirmacion_desde' => $i->aperturaReconfirmacion()->format('Y-m-d H:i'),
                'vencida' => $i->inicio()->isPast(),
                'asistencia' => $i->asistencia,
            ]);

        return Inertia::render('Feriante/MiCuenta', [
            'feriante' => $feriante,
            'inscripciones' => $inscripciones,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $feriante = Auth::guard('feriante')->user();

        if (! $feriante instanceof Feriante) {
            abort(401);
        }

        $data = $request->validate([
            'nombre' => 'required|string|max:100',
            'apellido' => 'required|string|max:100',
            'nombre_emprendimiento' => 'nullable|string|max:150',
            'categoria_id' => 'required|exists:categorias,id',
            'rubro_otro' => 'nullable|required_if:categoria_id,'.Categoria::idOtro().'|string|max:100',
            'telefono' => 'required|string|max:20',
            'instagram' => 'nullable|string|',
            'facebook' => 'nullable|string|',
            'tiktok' => 'nullable|string|',
            'consulta' => 'nullable|string|',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $feriante->update($data);

        return back()->with('success', 'Tus datos fueron actualizados.');

    }

    public function destroy(Request $request): RedirectResponse
    {
        $feriante = Auth::guard('feriante')->user();

        if (! $feriante instanceof Feriante) {
            abort(401);
        }

        Auth::guard('feriante')->logout();
        $feriante->delete();
        $request->session()->invalidate();

        return redirect('/')->with('success', 'Tu inscripción fue cancelada.');

    }
}
