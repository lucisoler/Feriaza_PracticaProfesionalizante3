<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('eventos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            // Base para calcular la hora de tolerancia (RN-07)
            $table->time('hora_inicio');
            // Solo los eventos "abierto" admiten inscripciones (RF-05)
            $table->enum('estado', ['borrador', 'abierto', 'cerrado', 'cancelado'])->default('borrador');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eventos');
    }
};
