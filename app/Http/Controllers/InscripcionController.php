<?php

namespace App\Http\Controllers;

use App\Models\Evento;
use App\Models\Feriante;
use App\Models\Inscripcion;
use App\Models\Lugar;
use App\Models\Puesto;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Inscripción del feriante a un evento: elige evento, día y una mesa de la
 * sección de su categoría en el plano del lugar (RF-06, RF-07; RN-03, RN-09).
 */
class InscripcionController extends Controller
{
    public function create(Request $request): Response
    {
        $feriante = $this->feriante();

        // Solo eventos abiertos con al menos un día por delante
        $eventos = Evento::with('lugar:id,nombre')
            ->where('estado', 'abierto')
            ->whereDate('fecha_fin', '>=', today())
            ->orderBy('fecha_inicio')
            ->get();

        $lugares = Lugar::whereIn('id', $eventos->pluck('lugar_id'))->get()
            ->map->conPlano()
            ->keyBy('id');

        $hoy = today()->toDateString();

        return Inertia::render('Feriante/Inscribirse', [
            'feriante' => $feriante->only(['id', 'nombre', 'categoria_id']) + ['categoria' => $feriante->categoria],
            'eventos' => $eventos->map(fn (Evento $e) => [
                'id' => $e->id,
                'nombre' => $e->nombre,
                'lugar_id' => $e->lugar_id,
                'lugar' => $e->lugar?->nombre,
                'hora_inicio' => substr($e->hora_inicio, 0, 5),
                'dias' => array_values(array_filter($e->dias(), fn ($d) => $d >= $hoy)),
                'ocupacion' => $e->ocupacion(),
            ]),
            'lugares' => $lugares,
            // Días en los que el feriante ya está inscripto, para no ofrecerlos de nuevo
            'inscriptas' => $feriante->inscripciones()
                ->whereIn('evento_id', $eventos->pluck('id'))
                ->get(['evento_id', 'fecha'])
                ->map(fn ($i) => "{$i->evento_id}|{$i->fecha->toDateString()}"),
            'eventoInicial' => $request->integer('evento') ?: null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $feriante = $this->feriante();

        $data = $request->validate([
            'evento_id' => 'required|exists:eventos,id',
            'fecha' => 'required|date_format:Y-m-d',
            'puesto_id' => 'nullable|integer',
            'acepta_reglamento' => 'accepted',
        ], [
            'acepta_reglamento.accepted' => 'Tenés que aceptar el reglamento y confirmar tu asistencia.',
        ]);

        $evento = Evento::findOrFail($data['evento_id']);

        if (! $evento->estaAbierto()) {
            throw ValidationException::withMessages(['evento_id' => 'Ese evento no tiene la inscripción abierta.']);
        }
        if (! in_array($data['fecha'], $evento->dias(), true) || $data['fecha'] < today()->toDateString()) {
            throw ValidationException::withMessages(['fecha' => 'Elegí uno de los días del evento.']);
        }

        // Revendedores (recibe_mesa = false) traen su mesa: no eligen puesto (RN-03)
        $recibeMesa = $feriante->categoria->recibe_mesa;

        $inscripcion = DB::transaction(function () use ($feriante, $evento, $data, $recibeMesa) {
            $yaInscripto = Inscripcion::where('evento_id', $evento->id)
                ->whereDate('fecha', $data['fecha'])
                ->where('feriante_id', $feriante->id)
                ->exists();

            if ($yaInscripto) {
                throw ValidationException::withMessages(['fecha' => 'Ya estás inscripto en este evento ese día.']);
            }

            $puesto = $recibeMesa ? $this->validarMesa($feriante, $evento, $data) : null;

            return Inscripcion::create([
                'evento_id' => $evento->id,
                'feriante_id' => $feriante->id,
                'puesto_id' => $puesto?->id,
                'fecha' => $data['fecha'],
                'estado' => 'pendiente',
                'confirmacion' => 'confirmada',
                'reglamento_aceptado_at' => now(),
            ]);
        });

        $inscripcion->load('evento.lugar', 'puesto');

        $numero = config('feria.whatsapp_coordinador');
        $mesa = $inscripcion->puesto ? "Mesa N° {$inscripcion->puesto->numero}" : 'Sin mesa (trae la suya)';
        $msg = urlencode(
            "Nueva inscripción:\n"
            . "{$feriante->nombre} {$feriante->apellido}\n"
            . "Categoría: {$feriante->categoria->nombre}\n"
            . "Evento: {$evento->nombre} ({$evento->lugar->nombre})\n"
            . 'Día: ' . $inscripcion->fecha->format('d/m/Y') . "\n"
            . "{$mesa}\n"
            . "Tel: {$feriante->telefono}"
        );

        return redirect('/gracias')->with('whatsapp', "https://wa.me/{$numero}?text={$msg}");
    }

    public function destroy(int $id): RedirectResponse
    {
        $inscripcion = $this->feriante()->inscripciones()->with('evento')->findOrFail($id);

        if (in_array($inscripcion->evento->estado, ['cerrado', 'cancelado'], true)) {
            return back()->withErrors(['inscripcion' => 'Ese evento ya terminó: la inscripción no se puede cancelar.']);
        }

        // Se borra para liberar la mesa de ese día (índice único evento + día + mesa)
        $inscripcion->delete();

        return back()->with('success', 'Cancelaste tu inscripción y se liberó la mesa.');
    }

    /** La mesa tiene que ser del lugar del evento, de la sección de su categoría y estar libre ese día. */
    private function validarMesa(Feriante $feriante, Evento $evento, array $data): Puesto
    {
        if (empty($data['puesto_id'])) {
            throw ValidationException::withMessages(['puesto_id' => 'Elegí una mesa del plano.']);
        }

        // lockForUpdate evita que dos personas reserven la misma mesa a la vez (RN-09)
        $puesto = Puesto::with('seccion')->lockForUpdate()->find($data['puesto_id']);

        $valida = $puesto
            && $puesto->lugar_id === $evento->lugar_id
            && $puesto->habilitado
            && $puesto->seccion?->disponible
            && $puesto->seccion->categoria_id === $feriante->categoria_id;

        if (! $valida) {
            throw ValidationException::withMessages([
                'puesto_id' => 'Esa mesa no está disponible para tu categoría. Elegí una de tu sección.',
            ]);
        }

        $ocupada = Inscripcion::where('evento_id', $evento->id)
            ->whereDate('fecha', $data['fecha'])
            ->where('puesto_id', $puesto->id)
            ->exists();

        if ($ocupada) {
            throw ValidationException::withMessages(['puesto_id' => 'Esa mesa ya fue reservada para ese día. Elegí otra.']);
        }

        return $puesto;
    }

    private function feriante(): Feriante
    {
        $feriante = Auth::guard('feriante')->user();

        if (! $feriante instanceof Feriante) {
            abort(401);
        }

        return $feriante;
    }
}
