<?php

namespace App\Console\Commands;

use App\Models\Inscripcion;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

/**
 * Una vez que termina el día de la feria, libera las mesas de quienes no
 * registraron el pago (RN-17). A quien sí pagó no se le toca nada.
 */
#[Signature('feria:liberar-mesas-sin-pago')]
#[Description('Elimina las inscripciones sin pago de los días de feria que ya terminaron')]
class LiberarMesasSinPago extends Command
{
    public function handle(): void
    {
        $borradas = Inscripcion::whereDate('fecha', '<', today())
            ->whereNull('pagado_at')
            ->delete();

        $this->info("Mesas liberadas por falta de pago: {$borradas}.");
    }
}
