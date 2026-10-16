<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inscripciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evento_id')->constrained('eventos')->cascadeOnDelete();
            $table->foreignId('feriante_id')->constrained('feriantes')->cascadeOnDelete();
            // Nulo para revendedores, que traen su propia mesa (RN-03)
            $table->foreignId('puesto_id')->nullable()->constrained('puestos')->nullOnDelete();
            $table->enum('estado', ['pendiente', 'aprobada', 'rechazada', 'cancelada'])->default('pendiente');
            $table->enum('confirmacion', ['sin_confirmar', 'confirmada', 'no_asiste'])->default('sin_confirmar');
            $table->enum('asistencia', ['pendiente', 'presente', 'ausente'])->default('pendiente');
            $table->dateTime('presente_at')->nullable();
            $table->dateTime('reglamento_aceptado_at');
            $table->timestamps();

            // Un feriante se inscribe una sola vez por evento
            $table->unique(['evento_id', 'feriante_id']);
            // Un puesto no se asigna a dos feriantes en el mismo evento (RN-09)
            $table->unique(['evento_id', 'puesto_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inscripciones');
    }
};
