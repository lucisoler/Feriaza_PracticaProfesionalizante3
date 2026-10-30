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
     * Inscribe a los feriantes aprobados en el primer día de cada evento
     * abierto. Cada uno ocupa una mesa de la sección de su categoría en el
     * lugar del evento; los revendedores se inscriben sin puesto (RN-03).
     */
    public function run(): void
    {
        $feriantes = Feriante::where('estado', 'aprobado')->orderBy('id')->get();

        foreach (Evento::where('estado', 'abierto')->get() as $evento) {
            $fecha = $evento->fecha_inicio->toDateString();
            $usadas = [];

            foreach ($feriantes as $feriante) {
                $puestoId = null;

                if ($feriante->categoria->recibe_mesa) {
                    $puestoId = Puesto::where('lugar_id', $evento->lugar_id)
                        ->where('habilitado', true)
                        ->whereHas('seccion', fn ($q) => $q->where('disponible', true)
                            ->where('categoria_id', $feriante->categoria_id))
                        ->whereNotIn('id', $usadas)
                        ->orderBy('numero')
                        ->value('id');

                    // Sin mesa libre en su sección, ese feriante no se inscribe
                    if (! $puestoId) {
                        continue;
                    }
                    $usadas[] = $puestoId;
                }

                Inscripcion::updateOrCreate(
                    ['evento_id' => $evento->id, 'fecha' => $fecha, 'feriante_id' => $feriante->id],
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
}
