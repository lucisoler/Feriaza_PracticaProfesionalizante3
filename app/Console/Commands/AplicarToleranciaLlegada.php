<?php

namespace App\Console\Commands;

use App\Models\Aviso;
use App\Models\Inscripcion;
use App\Models\Sancion;
use App\Notifications\AnulacionInscripcion;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Vencida la hora de tolerancia desde que empieza la feria, anula la
 * inscripción de quien no se presentó y libera la mesa (RN-07). Si el
 * evento es grande, además veta al feriante (RN-08).
 */
#[Signature('feria:aplicar-tolerancia')]
#[Description('Anula las inscripciones de hoy sin marcar presente vencida la hora de tolerancia')]
class AplicarToleranciaLlegada extends Command
{
    public function handle(): void
    {
        $afectadas = Inscripcion::whereDate('fecha', today())
            ->where('asistencia', 'pendiente')
            ->with('evento.lugar', 'feriante')
            ->get()
            ->filter(fn (Inscripcion $i) => $i->finTolerancia()->isPast());

        foreach ($afectadas as $inscripcion) {
            DB::transaction(function () use ($inscripcion) {
                $evento = $inscripcion->evento;
                $feriante = $inscripcion->feriante;

                $inscripcion->update(['asistencia' => 'ausente', 'estado' => 'cancelada', 'puesto_id' => null]);

                Sancion::create([
                    'inscripcion_id' => $inscripcion->id,
                    'motivo' => 'No se presentó dentro de la hora de tolerancia (automático).',
                ]);

                if ($evento->grande) {
                    $feriante->update([
                        'vetado_desde_evento_id' => $evento->id,
                        'vetado_motivo' => "no se presentó en {$evento->nombre} dentro de la tolerancia",
                        'vetado_at' => now(),
                    ]);
                }

                Aviso::create([
                    'mensaje' => "{$feriante->nombre} {$feriante->apellido} no se presentó dentro de la tolerancia en {$evento->nombre}: se anuló su inscripción".($evento->grande ? ' y queda vedado por 3 eventos.' : '.'),
                ]);

                try {
                    $feriante->notify(new AnulacionInscripcion($inscripcion));
                } catch (Throwable $e) {
                    report($e);
                }
            });
        }

        $this->info("Inscripciones anuladas por tolerancia vencida: {$afectadas->count()}.");
    }
}
