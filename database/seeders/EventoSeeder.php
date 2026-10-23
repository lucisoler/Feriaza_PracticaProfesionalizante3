<?php

namespace Database\Seeders;

use App\Models\Evento;
use Illuminate\Database\Seeder;

class EventoSeeder extends Seeder
{
    /**
     * Eventos de prueba (RNF-18): uno abierto para inscribirse,
     * uno cerrado (ya pasó) y uno en borrador.
     */
    public function run(): void
    {
        $eventos = [
            [
                'nombre' => 'Encuentro de danzas',
                'fecha_inicio' => now()->addDays(10)->toDateString(),
                'fecha_fin' => now()->addDays(11)->toDateString(),
                'hora_inicio' => '17:00',
                'estado' => 'abierto',
            ],
            [
                'nombre' => 'Feria de la primavera',
                'fecha_inicio' => now()->subDays(20)->toDateString(),
                'fecha_fin' => now()->subDays(20)->toDateString(),
                'hora_inicio' => '16:00',
                'estado' => 'cerrado',
            ],
            [
                'nombre' => 'Día de la tradición',
                'fecha_inicio' => now()->addDays(30)->toDateString(),
                'fecha_fin' => now()->addDays(30)->toDateString(),
                'hora_inicio' => '18:00',
                'estado' => 'borrador',
            ],
        ];

        foreach ($eventos as $datos) {
            Evento::updateOrCreate(['nombre' => $datos['nombre']], $datos);
        }
    }
}
