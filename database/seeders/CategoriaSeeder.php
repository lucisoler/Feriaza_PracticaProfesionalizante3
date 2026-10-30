<?php

namespace Database\Seeders;

use App\Models\Categoria;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class CategoriaSeeder extends Seeder
{
    /** Color distintivo de cada categoría en los planos (RF-19). */
    public const COLORES = [
        'Artesanos'    => '#b4532a',
        'Manualidades' => '#7b4f9e',
        'Masas'        => '#d0901c',
        'Otro'         => '#2a7f8f',
        'Revendedores' => '#c2457a',
    ];

    /**
     * Categorías de feriantes con su prioridad (RN-01, RN-02, RN-03).
     * La prioridad de "Otro" es un supuesto a confirmar con el coordinador (S-04).
     */
    public function run(): void
    {
        $categorias = [
            ['nombre' => 'Artesanos',    'prioridad' => 1, 'recibe_mesa' => true],
            ['nombre' => 'Manualidades', 'prioridad' => 1, 'recibe_mesa' => true],
            ['nombre' => 'Masas',        'prioridad' => 2, 'recibe_mesa' => true],
            ['nombre' => 'Otro',         'prioridad' => 2, 'recibe_mesa' => true],
            ['nombre' => 'Revendedores', 'prioridad' => 3, 'recibe_mesa' => false],
        ];

        // La migración de categorías corre este seeder antes de que exista la columna "color"
        $conColor = Schema::hasColumn('categorias', 'color');

        foreach ($categorias as $categoria) {
            if ($conColor) {
                $categoria['color'] = self::COLORES[$categoria['nombre']];
            }

            Categoria::updateOrCreate(
                ['nombre' => $categoria['nombre']],
                $categoria
            );
        }
    }
}
