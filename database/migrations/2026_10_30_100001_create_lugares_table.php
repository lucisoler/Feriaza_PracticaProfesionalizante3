<?php

use Database\Seeders\LugarSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Lugares donde se arma la feria (Plaza Central y Pulmón Verde) y las
     * secciones de cada plano (RF-19). Las medidas están en unidades del
     * plano digitalizado (el viewBox del SVG), no en metros.
     */
    public function up(): void
    {
        Schema::create('lugares', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->string('slug', 50)->unique();
            $table->unsignedSmallInteger('ancho')->default(1000);
            $table->unsignedSmallInteger('alto')->default(700);
            $table->timestamps();
        });

        Schema::create('secciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lugar_id')->constrained('lugares')->cascadeOnDelete();
            $table->string('nombre', 60);
            // Nula = sección sin categoría asignada todavía
            $table->foreignId('categoria_id')->nullable()->constrained('categorias')->nullOnDelete();
            // Falso = zona no disponible (monumento, juegos, laguna...)
            $table->boolean('disponible')->default(true);
            $table->unsignedSmallInteger('x');
            $table->unsignedSmallInteger('y');
            $table->unsignedSmallInteger('ancho');
            $table->unsignedSmallInteger('alto');
            $table->timestamps();
        });

        // Igual que las categorías: los dos planos son datos base del sistema
        (new LugarSeeder())->run();
    }

    public function down(): void
    {
        Schema::dropIfExists('secciones');
        Schema::dropIfExists('lugares');
    }
};
