<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\Lugar;
use App\Models\Seccion;
use Illuminate\Database\Seeder;

class LugarSeeder extends Seeder
{
    /**
     * Planos digitalizados de la Plaza Central de Juan José Castelli y del
     * Pulmón Verde, con sus secciones iniciales (RF-19). Las coordenadas
     * coinciden con el dibujo de base de cada plano (resources/js/Components/planos).
     * El coordinador después ajusta secciones y mesas desde el panel.
     *
     * Cada sección: [nombre, categoría o null, disponible, x, y, ancho, alto]
     */
    private const LUGARES = [
        'plaza-central' => [
            'nombre' => 'Plaza Central',
            'secciones' => [
                ['Vereda norte', 'Artesanos', true, 130, 72, 740, 56],
                ['Vereda sur', 'Manualidades', true, 130, 572, 740, 56],
                ['Costado oeste', 'Masas', true, 72, 160, 62, 380],
                ['Costado este', 'Otro', true, 866, 160, 62, 380],
                ['Rincón de revendedores', 'Revendedores', true, 640, 170, 190, 100],
                ['Patio sudeste', null, true, 640, 430, 190, 110],
                ['Monumento central', null, false, 420, 270, 160, 160],
                ['Escenario', null, false, 170, 160, 190, 100],
                ['Juegos infantiles', null, false, 170, 430, 190, 110],
            ],
        ],
        'pulmon-verde' => [
            'nombre' => 'Pulmón Verde',
            'secciones' => [
                ['Paseo de artesanos', 'Artesanos', true, 110, 100, 360, 60],
                ['Patio de comidas', 'Masas', true, 560, 100, 330, 84],
                ['Sendero de manualidades', 'Manualidades', true, 110, 520, 380, 60],
                ['Feria libre', 'Otro', true, 560, 500, 320, 70],
                ['Revendedores', 'Revendedores', true, 120, 250, 150, 160],
                ['Laguna', null, false, 400, 250, 270, 170],
                ['Playón deportivo', null, false, 730, 250, 160, 150],
            ],
        ],
    ];

    public function run(): void
    {
        $categorias = Categoria::pluck('id', 'nombre');

        foreach (self::LUGARES as $slug => $datos) {
            $lugar = Lugar::updateOrCreate(['slug' => $slug], ['nombre' => $datos['nombre']]);

            // Solo se cargan las secciones si el lugar todavía no tiene: no se pisa lo que diseñó el coordinador
            if ($lugar->secciones()->exists()) {
                continue;
            }

            foreach ($datos['secciones'] as [$nombre, $categoria, $disponible, $x, $y, $ancho, $alto]) {
                Seccion::create([
                    'lugar_id' => $lugar->id,
                    'nombre' => $nombre,
                    'categoria_id' => $categoria ? $categorias[$categoria] : null,
                    'disponible' => $disponible,
                    'x' => $x,
                    'y' => $y,
                    'ancho' => $ancho,
                    'alto' => $alto,
                ]);
            }
        }
    }
}
