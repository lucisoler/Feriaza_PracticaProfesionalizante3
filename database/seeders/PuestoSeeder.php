<?php

namespace Database\Seeders;

use App\Models\Lugar;
use App\Models\Puesto;
use Illuminate\Database\Seeder;

class PuestoSeeder extends Seeder
{
    /** Mesas por sección en los datos de prueba. */
    private const MESAS_POR_SECCION = 10;

    /**
     * Distribuye mesas en grilla dentro de cada sección disponible cuya
     * categoría recibe mesa. Requiere LugarSeeder y CategoriaSeeder.
     */
    public function run(): void
    {
        foreach (Lugar::with('secciones.categoria', 'secciones.puestos')->get() as $lugar) {
            foreach ($lugar->secciones as $seccion) {
                if (! $seccion->disponible || ! $seccion->categoria?->recibe_mesa) {
                    continue;
                }

                $faltan = self::MESAS_POR_SECCION - $seccion->puestos->count();
                $ocupadas = $seccion->puestos->map->only(['x', 'y'])->all();

                foreach ($seccion->posicionesLibres(max(0, $faltan), $ocupadas) as $punto) {
                    Puesto::create([
                        'lugar_id' => $lugar->id,
                        'seccion_id' => $seccion->id,
                        'numero' => $lugar->siguienteNumero(),
                        'estado' => 'libre',
                    ] + $punto);
                }
            }
        }
    }
}
