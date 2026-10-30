<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Carga todos los datos de prueba del sistema, en orden.
     */
    public function run(): void
    {
        $this->call([
            CoordinadorSeeder::class,
            CategoriaSeeder::class,
            LugarSeeder::class,
            PuestoSeeder::class,
            FerianteSeeder::class,
            EventoSeeder::class,
            InscripcionSeeder::class,
        ]);
    }
}
