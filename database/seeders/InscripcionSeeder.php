<?php

namespace Database\Seeders;

use App\Models\Evento;
use App\Models\Feriante;
use App\Models\Inscripcion;
use App\Models\Puesto;
use Illuminate\Database\Seeder;

class InscripcionSeeder extends Seeder
{
    /**
     * Inscribe a los feriantes aprobados en el evento abierto.
     * Los que reciben mesa ocupan los puestos en orden; los revendedores
     * se inscriben sin puesto (RN-03).
     */
    public function run(): void
    {
        $evento = Evento::where('estado', 'abierto')->first();
        if (! $evento) {
            return;
        }

        $puestos = Puesto::where('habilitado', true)->orderBy('numero')->pluck('id')->all();
        $siguiente = 0;

        $feriantes = Feriante::where('estado', 'aprobado')->orderBy('id')->get();

        foreach ($feriantes as $feriante) {
            $puestoId = null;
            if ($feriante->categoria->recibe_mesa && isset($puestos[$siguiente])) {
                $puestoId = $puestos[$siguiente++];
            }

            Inscripcion::updateOrCreate(
                ['evento_id' => $evento->id, 'feriante_id' => $feriante->id],
                [
                    'puesto_id' => $puestoId,
                    'estado' => 'aprobada',
                    'confirmacion' => $feriante->asistencia_confirmada ? 'confirmada' : 'sin_confirmar',
                    'reglamento_aceptado_at' => now(),
                ]
            );
        }
    }
}
