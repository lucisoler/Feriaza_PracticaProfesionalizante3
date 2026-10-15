<?php

use Database\Seeders\CategoriaSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categorias', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 50)->unique();
        
            $table->unsignedTinyInteger('prioridad');
        
            $table->boolean('recibe_mesa')->default(true);
            $table->timestamps();
        });

        
        (new CategoriaSeeder())->run();
    }

    public function down(): void
    {
        Schema::dropIfExists('categorias');
    }
};
