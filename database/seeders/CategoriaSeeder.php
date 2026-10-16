<?php

namespace Database\Seeders;

use App\Models\Categoria;
use Illuminate\Database\Seeder;

class CategoriaSeeder extends Seeder
{
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

        foreach ($categorias as $categoria) {
            Categoria::updateOrCreate(
                ['nombre' => $categoria['nombre']],
                $categoria
            );
        }
    }
}
