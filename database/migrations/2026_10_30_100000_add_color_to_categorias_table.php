<?php

use Database\Seeders\CategoriaSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Color distintivo de cada categoría en los planos (RF-19). */
    public function up(): void
    {
        Schema::table('categorias', function (Blueprint $table) {
            $table->string('color', 7)->default('#8a8a7a')->after('recibe_mesa');
        });

        foreach (CategoriaSeeder::COLORES as $nombre => $color) {
            DB::table('categorias')->where('nombre', $nombre)->update(['color' => $color]);
        }
    }

    public function down(): void
    {
        Schema::table('categorias', function (Blueprint $table) {
            $table->dropColumn('color');
        });
    }
};
