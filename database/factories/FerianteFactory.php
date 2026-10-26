<?php

namespace Database\Factories;

use App\Models\Categoria;
use App\Models\Feriante;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Feriante>
 */
class FerianteFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nombre' => fake()->firstName(),
            'apellido' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'password' => 'password',
            'nombre_emprendimiento' => fake()->company(),
            // Usa una categoría existente o crea una que recibe mesa
            'categoria_id' => fn () => Categoria::where('recibe_mesa', true)->value('id')
                ?? Categoria::create(['nombre' => 'Artesanos', 'prioridad' => 1, 'recibe_mesa' => true])->id,
            'telefono' => '3624' . fake()->numerify('######'),
            'estado' => 'pendiente',
        ];
    }
}
