<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CoordinadorSeeder extends Seeder
{

    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'coordinador@feriaza.test'],
            [
                'name'     => 'Coordinador de Prueba',
                'password' => Hash::make('Coordinador123'),
            ]
        );
    }
}

