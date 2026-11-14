<?php

namespace App\Http\Controllers;

use App\Models\Evento;
use App\Models\Inscripcion;
use App\Models\Lugar;
use App\Services\PlanillaPdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
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
                'asistencia' => $i->asistencia,
                'puede_marcar_presente' => $i->puedeMarcarsePresente(),
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

    /** Planilla en PDF con los feriantes inscriptos (no cancelados ni rechazados) en un día del evento. */
    public function planilla(Request $request, Evento $evento, PlanillaPdf $planillaPdf): \Illuminate\Http\Response
    {
        $dias = $evento->dias();
        $dia = in_array($request->query('dia'), $dias, true) ? $request->query('dia') : $dias[0];

        $inscripciones = $evento->inscripciones()
            ->whereDate('fecha', $dia)
            ->whereNotIn('estado', ['cancelada', 'rechazada'])
            ->with(['feriante:id,nombre,apellido,telefono,categoria_id,nombre_emprendimiento', 'feriante.categoria:id,nombre', 'puesto:id,numero'])
            ->get()
            ->sortBy(fn (Inscripcion $i) => [$i->puesto?->numero ?? 9999, $i->feriante->apellido])
            ->values();

        $filas = $inscripciones->map(fn (Inscripcion $i) => [
            $i->puesto?->numero ?? '-',
            $i->feriante->apellido,
            $i->feriante->nombre,
            $i->feriante->nombre_emprendimiento ?? '',
            $i->feriante->telefono ?? '',
            $i->feriante->categoria?->nombre ?? 'Sin categoría',
            match ($i->confirmacion) {
                'confirmada' => 'Confirmada',
                'no_asiste' => 'No asiste',
                default => 'Sin confirmar',
            },
        ])->all();

        $fecha = Carbon::parse($dia)->locale('es')->isoFormat('dddd D [de] MMMM [de] YYYY');
        $encabezado = [
            'Planilla de feriantes inscriptos',
            "Evento: {$evento->nombre} · {$evento->lugar->nombre}",
            ucfirst("Día: {$fecha} · desde las ".substr($evento->hora_inicio, 0, 5).' h'),
            'Total: '.$inscripciones->count().' inscriptos',
        ];

        $columnas = [
            ['titulo' => 'Mesa', 'ancho' => 45],
            ['titulo' => 'Apellido', 'ancho' => 120],
            ['titulo' => 'Nombre', 'ancho' => 120],
            ['titulo' => 'Emprendimiento', 'ancho' => 170],
            ['titulo' => 'Teléfono', 'ancho' => 95],
            ['titulo' => 'Categoría', 'ancho' => 120],
            ['titulo' => 'Confirmación', 'ancho' => 100],
        ];

        $nombreArchivo = Str::slug("planilla {$evento->nombre} {$dia}").'.pdf';

        return response($planillaPdf->generar($encabezado, $columnas, $filas), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "attachment; filename=\"{$nombreArchivo}\"",
        ]);
    }

    /** El coordinador acepta o rechaza la inscripción de un feriante a un evento. Rechazar libera la mesa. */
    public function cambiarEstadoInscripcion(Request $request, Inscripcion $inscripcion): RedirectResponse
    {
        $data = $request->validate([
            'estado' => 'required|in:aprobada,rechazada',
        ]);

        $inscripcion->update([
            'estado' => $data['estado'],
            'puesto_id' => $data['estado'] === 'rechazada' ? null : $inscripcion->puesto_id,
        ]);

        return back()->with('success', $data['estado'] === 'aprobada' ? 'Inscripción aceptada.' : 'Inscripción rechazada.');
    }

    /** El coordinador marca presente dentro de la hora de tolerancia (RN-07, RF-11). */
    public function marcarPresente(Inscripcion $inscripcion): RedirectResponse
    {
        if (! $inscripcion->puedeMarcarsePresente()) {
            return back()->withErrors(['inscripcion' => 'Solo se marca presente desde que empieza la feria y durante la hora de tolerancia.']);
        }

        $inscripcion->update(['asistencia' => 'presente', 'presente_at' => now()]);

        return back()->with('success', 'Presente registrado.');
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
            // Define si la inasistencia veta al feriante (RN-07, RN-08)
            'grande' => 'sometimes|boolean',
        ]);
    }
}
