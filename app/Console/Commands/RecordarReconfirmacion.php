<?php

namespace App\Console\Commands;

use App\Models\Inscripcion;
use App\Notifications\RecordatorioReconfirmacion;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Throwable;

/**
 * Avisa por correo a los feriantes cuya reconfirmación recién se abrió, para
 * que reconfirmen el puesto y elijan el medio de pago (RN-16).
 */
#[Signature('feria:recordar-reconfirmacion')]
#[Description('Manda el correo de reconfirmación a los feriantes que ya pueden reconfirmar su puesto')]
class RecordarReconfirmacion extends Command
{
    public function handle(): void
    {
        $inscripciones = Inscripcion::where('confirmacion', 'sin_confirmar')
            ->whereNull('recordatorio_reconfirmacion_enviado_at')
            ->with('evento.lugar', 'feriante', 'puesto')
            ->get()
            ->filter(fn (Inscripcion $i) => $i->debeAvisarseReconfirmacion());

        foreach ($inscripciones as $inscripcion) {
            try {
                $inscripcion->feriante->notify(new RecordatorioReconfirmacion($inscripcion));
                $inscripcion->update(['recordatorio_reconfirmacion_enviado_at' => now()]);
            } catch (Throwable $e) {
                report($e);
            }
        }

        $this->info("Recordatorios de reconfirmación enviados: {$inscripciones->count()}.");
    }
}
