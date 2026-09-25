<?php

namespace Database\Seeders;

use App\Models\Puesto;
use Illuminate\Database\Seeder;

class PuestoSeeder extends Seeder
{

    public function run(): void
    {
        for ($i = 1; $i <= 30; $i++) {
            Puesto::updateOrCreate(
                ['numero' => $i],
                ['estado' => 'libre']
            );
        }
    }
}
