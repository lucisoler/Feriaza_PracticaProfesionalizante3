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
                ['Vereda norte', 'Artesanos', true, 290, 8, 420, 52],
                ['Vereda sur', 'Manualidades', true, 290, 644, 420, 52],
                ['Costado oeste', 'Masas', true, 176, 190, 52, 340],
                ['Costado este', 'Otro', true, 800, 360, 52, 340],
                ['Rincón de revendedores', 'Revendedores', true, 850, 70, 70, 52],
                ['Patio sudeste', null, true, 600, 520, 160, 90],
                ['Monumento central', null, false, 420, 280, 160, 130],
                ['Escenario', null, false, 320, 150, 160, 90],
                ['Juegos infantiles', null, false, 340, 470, 160, 100],
            ],
        ],
        'pulmon-verde' => [
            'nombre' => 'Pulmón Verde',
            'secciones' => [
                ['Paseo de artesanos', 'Artesanos', true, 140, 110, 352, 46],
                ['Patio de comidas', 'Masas', true, 560, 180, 352, 46],
                ['Sendero de manualidades', 'Manualidades', true, 160, 200, 46, 280],
                ['Feria libre', 'Otro', true, 530, 640, 352, 46],
                ['Revendedores', 'Revendedores', true, 720, 250, 46, 200],
                ['Laguna', null, false, 420, 300, 160, 110],
                ['Playón deportivo', null, false, 745, 60, 150, 100],
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
