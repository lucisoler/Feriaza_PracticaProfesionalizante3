<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sanciones', function (Blueprint $table) {
            $table->id();
            // Una sanción por ausencia (RN-08)
            $table->foreignId('inscripcion_id')->unique()->constrained('inscripciones')->cascadeOnDelete();
            // Coordinador que la registró
            $table->foreignId('registrada_por')->constrained('users');
            $table->string('motivo', 255);
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sanciones');
    }
};
