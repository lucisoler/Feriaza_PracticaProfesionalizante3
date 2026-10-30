<?php

namespace App\Http\Controllers;

use App\Models\Evento;
use App\Models\Inscripcion;
use App\Models\Lugar;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Gestión de eventos por el coordinador: lugar, días y estado (RF-05), y
 * ocupación de las mesas del plano día por día (RF-10).
 */
class EventoController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Coordinador/Eventos', [
            'eventos' => Evento::with('lugar:id,nombre')
                ->withCount('inscripciones')
                ->orderByDesc('fecha_inicio')
                ->get(),
            'lugares' => Lugar::orderBy('id')->get(['id', 'nombre']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Evento::create($this->validar($request));

        return back()->with('success', 'Evento creado.');
    }

    public function update(Request $request, Evento $evento): RedirectResponse
    {
        $data = $this->validar($request);

        // Cambiar de lugar dejaría las mesas reservadas apuntando al plano equivocado
        if ($data['lugar_id'] != $evento->lugar_id && $evento->inscripciones()->whereNotNull('puesto_id')->exists()) {
            return back()->withErrors(['lugar_id' => 'El evento ya tiene mesas reservadas: no se puede cambiar de lugar.']);
        }

        $evento->update($data);

        return back()->with('success', 'Evento actualizado.');
    }

    public function destroy(Evento $evento): RedirectResponse
    {
        // Un evento con inscriptos no se elimina, solo se cancela (RF-05)
        if ($evento->inscripciones()->exists()) {
            return back()->withErrors(['evento' => 'El evento tiene inscriptos: cambialo a "cancelado" en lugar de eliminarlo.']);
        }

        $evento->delete();

        return redirect('/coordinador/eventos')->with('success', 'Evento eliminado.');
    }

    public function show(Request $request, Evento $evento): Response
    {
        $dias = $evento->dias();
        $dia = in_array($request->query('dia'), $dias, true) ? $request->query('dia') : $dias[0];

        $inscripciones = $evento->inscripciones()
            ->whereDate('fecha', $dia)
            ->with(['feriante:id,nombre,apellido,telefono,categoria_id,nombre_emprendimiento', 'puesto:id,numero'])
            ->get()
            ->map(fn (Inscripcion $i) => [
                'id' => $i->id,
                'estado' => $i->estado,
                'confirmacion' => $i->confirmacion,
                'puesto_id' => $i->puesto_id,
                'mesa' => $i->puesto?->numero,
                'feriante' => $i->feriante,
            ]);

        return Inertia::render('Coordinador/Evento', [
            'evento' => $evento->load('lugar:id,nombre'),
            'dias' => $dias,
            'dia' => $dia,
            'lugar' => $evento->lugar->conPlano(),
            'inscripciones' => $inscripciones,
        ]);
    }

    private function validar(Request $request): array
    {
        return $request->validate([
            'nombre' => 'required|string|max:100',
            'lugar_id' => 'required|exists:lugares,id',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'hora_inicio' => 'required|date_format:H:i',
            'estado' => 'required|in:borrador,abierto,cerrado,cancelado',
        ]);
    }
}
