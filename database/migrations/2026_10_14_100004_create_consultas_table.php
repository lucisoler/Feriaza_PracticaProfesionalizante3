<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consultas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('feriante_id')->constrained('feriantes')->cascadeOnDelete();
            $table->string('asunto', 100);
            $table->text('mensaje');
            $table->text('respuesta')->nullable();
            $table->enum('estado', ['pendiente', 'respondida'])->default('pendiente');
            $table->foreignId('respondida_por')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('respondida_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consultas');
    }
};
