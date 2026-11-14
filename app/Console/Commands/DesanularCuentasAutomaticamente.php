<?php

namespace App\Console\Commands;

use App\Models\Aviso;
use App\Models\Evento;
use App\Models\Feriante;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

/**
 * Desanula sola la cuenta cuando pasaron los eventos reales requeridos desde la
 * anulación: 3 si la falta fue en un evento grande, 2 si fue en uno regular.
 */
#[Signature('feria:desanular-cuentas')]
#[Description('Desanula las cuentas anuladas que ya cumplieron los eventos requeridos')]
class DesanularCuentasAutomaticamente extends Command
{
    public function handle(): void
    {
        $desanuladas = 0;

        Feriante::where('estado', 'anulado')
            ->whereNotNull('anulado_at')
            ->get()
            ->each(function (Feriante $feriante) use (&$desanuladas) {
                $eventosReales = Evento::whereIn('estado', ['abierto', 'cerrado'])
                    ->whereDate('fecha_inicio', '>', $feriante->anulado_at->toDateString())
                    ->count();

                if ($eventosReales < $feriante->eventosParaDesanular()) {
                    return;
                }

                $feriante->update([
                    'estado' => 'aprobado',
                    'motivo_anulacion' => null,
                    'anulado_at' => null,
                ]);

                Aviso::create([
                    'mensaje' => "{$feriante->nombre} {$feriante->apellido} fue desanulado automáticamente tras {$eventosReales} eventos.",
                ]);

                $desanuladas++;
            });

        $this->info("Cuentas desanuladas automáticamente: {$desanuladas}.");
    }
}
