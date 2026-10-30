<?php

namespace Database\Seeders;

use App\Models\Evento;
use App\Models\Lugar;
use Illuminate\Database\Seeder;

class EventoSeeder extends Seeder
{
    /**
     * Eventos de prueba (RNF-18): uno abierto para inscribirse,
     * uno cerrado (ya pasó) y uno en borrador, repartidos entre la Plaza
     * Central y el Pulmón Verde. Requiere LugarSeeder.
     */
    public function run(): void
    {
        $lugares = Lugar::pluck('id', 'slug');

        $eventos = [
            [
                'nombre' => 'Encuentro de danzas',
                'lugar_id' => $lugares['plaza-central'],
                'fecha_inicio' => now()->addDays(10)->toDateString(),
                'fecha_fin' => now()->addDays(11)->toDateString(),
                'hora_inicio' => '17:00',
                'estado' => 'abierto',
            ],
            [
                'nombre' => 'Feria de la primavera',
                'lugar_id' => $lugares['pulmon-verde'],
                'fecha_inicio' => now()->subDays(20)->toDateString(),
                'fecha_fin' => now()->subDays(20)->toDateString(),
                'hora_inicio' => '16:00',
                'estado' => 'cerrado',
            ],
            [
                'nombre' => 'Día de la tradición',
                'lugar_id' => $lugares['plaza-central'],
                'fecha_inicio' => now()->addDays(30)->toDateString(),
                'fecha_fin' => now()->addDays(30)->toDateString(),
                'hora_inicio' => '18:00',
                'estado' => 'borrador',
            ],
            [
                'nombre' => 'Feria verde de fin de mes',
                'lugar_id' => $lugares['pulmon-verde'],
                'fecha_inicio' => now()->addDays(15)->toDateString(),
                'fecha_fin' => now()->addDays(15)->toDateString(),
                'hora_inicio' => '17:30',
                'estado' => 'abierto',
            ],
        ];

        foreach ($eventos as $datos) {
            Evento::updateOrCreate(['nombre' => $datos['nombre']], $datos);
        }
    }
}
