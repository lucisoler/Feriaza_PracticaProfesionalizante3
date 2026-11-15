<?php

namespace App\Http\Controllers;

use App\Models\Evento;
use App\Models\Inscripcion;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Panel de asistencia del día de la feria: el coordinador marca presentes a
 * los inscriptos en orden de llegada. Vencida la tolerancia, quien no llegó
 * queda ausente automáticamente (feria:aplicar-tolerancia, RN-07).
 */
class AsistenciaController extends Controller
{
    public function index(Request $request): Response
    {
        $eventos = Evento::with('lugar:id,nombre')
            ->whereIn('estado', ['abierto', 'cerrado'])
            ->orderByDesc('fecha_inicio')
            ->get();

        $evento = $eventos->firstWhere('id', $request->integer('evento')) ?? $eventos->first();

        if (! $evento) {
            return Inertia::render('Coordinador/Asistencia', [
                'eventos' => [],
                'evento' => null,
                'dias' => [],
                'dia' => null,
                'inscripciones' => [],
            ]);
        }

        $dias = $evento->dias();
        $dia = in_array($request->query('dia'), $dias, true) ? $request->query('dia') : $dias[0];

        $inscripciones = $evento->inscripciones()
            ->whereDate('fecha', $dia)
            ->whereNotIn('estado', ['cancelada', 'rechazada'])
            ->with(['feriante:id,nombre,apellido,telefono,nombre_emprendimiento,estado', 'puesto:id,numero'])
            ->orderBy('numero')
            ->get()
            ->map(fn (Inscripcion $i) => [
                'id' => $i->id,
                'numero' => $i->numero,
                'mesa' => $i->puesto?->numero,
                'asistencia' => $i->asistencia,
                'puede_marcar_presente' => $i->puedeMarcarsePresente(),
                'hora_fin_tolerancia' => $i->finTolerancia()->format('H:i'),
                'feriante' => $i->feriante,
            ]);

        return Inertia::render('Coordinador/Asistencia', [
            'eventos' => $eventos->map(fn (Evento $e) => [
                'id' => $e->id,
                'nombre' => $e->nombre,
                'lugar' => $e->lugar?->nombre,
                'dias' => $e->dias(),
                'hora_inicio' => substr($e->hora_inicio, 0, 5),
            ]),
            'evento' => ['id' => $evento->id, 'nombre' => $evento->nombre, 'hora_inicio' => substr($evento->hora_inicio, 0, 5), 'lugar' => $evento->lugar?->nombre],
            'dias' => $dias,
            'dia' => $dia,
            'inscripciones' => $inscripciones,
        ]);
    }
}
