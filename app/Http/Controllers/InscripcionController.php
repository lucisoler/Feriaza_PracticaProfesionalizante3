<?php

namespace App\Http\Controllers;

use App\Models\Aviso;
use App\Models\Evento;
use App\Models\Feriante;
use App\Models\Inscripcion;
use App\Models\Lugar;
use App\Models\Puesto;
use App\Models\User;
use App\Notifications\NuevaInscripcion;
use App\Notifications\ReconfirmacionRecibida;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

/**
 * Inscripción del feriante a un evento: elige evento, día y una mesa de la
 * sección de su categoría en el plano del lugar (RF-06, RF-07; RN-03, RN-09).
 * Se abre dos semanas antes del evento y el puesto se reconfirma 72 horas
 * antes (RN-15, RN-16).
 */
class InscripcionController extends Controller
{
    public function create(Request $request): Response|RedirectResponse
    {
        $feriante = $this->feriante();

        if ($feriante->estaAnulado()) {
            return redirect('/mi-cuenta')->withErrors(['inscripcion' => 'Tu cuenta está anulada: no podés inscribirte a nuevos eventos.']);
        }
        if ($feriante->estaVetado()) {
            return redirect('/mi-cuenta')->withErrors(['inscripcion' => "Estás vedado por inasistencia ({$feriante->vetado_motivo}): todavía no podés inscribirte a nuevos eventos."]);
        }

        // Solo eventos abiertos, con al menos un día por delante y cuya inscripción ya se abrió (RN-15)
        $eventos = Evento::with('lugar:id,nombre')
            ->where('estado', 'abierto')
            ->whereDate('fecha_fin', '>=', today())
            ->whereDate('fecha_inicio', '<=', today()->addDays(config('feria.dias_apertura_inscripcion')))
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

        if ($feriante->estaAnulado()) {
            throw ValidationException::withMessages(['evento_id' => 'Tu cuenta está anulada: no podés inscribirte a nuevos eventos.']);
        }
        if ($feriante->estaVetado()) {
            throw ValidationException::withMessages(['evento_id' => "Estás vedado por inasistencia ({$feriante->vetado_motivo}): todavía no podés inscribirte a nuevos eventos."]);
        }
        if (! $evento->estaAbierto()) {
            throw ValidationException::withMessages(['evento_id' => 'Ese evento no tiene la inscripción abierta.']);
        }
        if (! $evento->inscripcionHabilitada()) {
            throw ValidationException::withMessages([
                'evento_id' => 'La inscripción a ese evento se abre el '.$evento->aperturaInscripcion()->format('d/m/Y').'.',
            ]);
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
                // Se confirma después, 72 horas antes del evento (RN-16)
                'confirmacion' => 'sin_confirmar',
                'reglamento_aceptado_at' => now(),
            ]);
        });

        $inscripcion->load('evento.lugar', 'puesto', 'feriante.categoria');

        // Aviso en el panel del coordinador: quién se inscribió y para cuándo.
        $evento = $inscripcion->evento;
        $mesa = $inscripcion->puesto ? " Mesa N° {$inscripcion->puesto->numero}." : '';

        Aviso::create([
            'mensaje' => "{$inscripcion->feriante->nombre} {$inscripcion->feriante->apellido} se inscribió a {$evento->nombre} el {$inscripcion->fecha->format('d/m')} en {$evento->lugar->nombre}.{$mesa}",
        ]);

        // Aviso por correo a los coordinadores (RF-15). Si el correo falla, la inscripción igual queda hecha.
        try {
            Notification::send(User::all(), new NuevaInscripcion($inscripcion));
        } catch (Throwable $e) {
            report($e);
        }

        return redirect('/gracias');
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

    /**
     * El feriante cede su lugar el mismo día de la feria (ej.: se va antes de que termine).
     * A diferencia de cancelar, se puede hacer aunque el evento ya esté cerrado, y avisa
     * al coordinador en el panel para que pueda ofrecer la mesa a otra persona.
     */
    public function cederLugar(int $id): RedirectResponse
    {
        $inscripcion = $this->feriante()->inscripciones()->with('evento.lugar', 'puesto', 'feriante')->findOrFail($id);

        if (! $inscripcion->fecha->isToday()) {
            return back()->withErrors(['inscripcion' => 'Solo podés ceder tu lugar el mismo día de la feria.']);
        }

        $mesa = $inscripcion->puesto ? " (mesa N° {$inscripcion->puesto->numero})" : '';
        Aviso::create([
            'mensaje' => "{$inscripcion->feriante->nombre} {$inscripcion->feriante->apellido} cedió su lugar en {$inscripcion->evento->nombre}{$mesa}.",
        ]);

        // Se borra para liberar la mesa al instante (índice único evento + día + mesa)
        $inscripcion->delete();

        return back()->with('success', 'Cediste tu lugar y avisamos al coordinador.');
    }

    /** Reconfirmación del puesto 72 horas antes del día que le toca (RN-16). */
    public function reconfirmar(int $id): RedirectResponse
    {
        $inscripcion = $this->feriante()->inscripciones()->with('evento.lugar', 'feriante')->findOrFail($id);

        if ($inscripcion->confirmacion !== 'sin_confirmar') {
            return back()->withErrors(['inscripcion' => 'Esa inscripción ya fue reconfirmada.']);
        }
        if (! $inscripcion->puedeReconfirmar()) {
            return back()->withErrors([
                'inscripcion' => 'La reconfirmación se habilita el '.$inscripcion->aperturaReconfirmacion()->format('d/m/Y').' a las '.$inscripcion->aperturaReconfirmacion()->format('H:i').' y cierra al empezar la feria.',
            ]);
        }

        $inscripcion->update([
            'confirmacion' => 'confirmada',
            'reconfirmada_at' => now(),
        ]);

        try {
            $inscripcion->feriante->notify(new ReconfirmacionRecibida($inscripcion));
        } catch (Throwable $e) {
            report($e);
        }

        return back()->with('success', 'Reconfirmaste tu puesto. ¡Te esperamos!');
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
