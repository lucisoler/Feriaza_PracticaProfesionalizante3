<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * La mesa fija por feriante del flujo original se reemplazó por las
     * inscripciones por evento (issue #48, docs/modelo-de-datos.md sección 5).
     * `habilitado` no se toca: sigue marcando si la mesa existe en el plano.
     */
    public function up(): void
    {
        Schema::table('puestos', function (Blueprint $table) {
            $table->dropForeign(['feriante_id']);
            $table->dropColumn(['estado', 'feriante_id']);
        });
    }

    public function down(): void
    {
        Schema::table('puestos', function (Blueprint $table) {
            $table->enum('estado', ['libre', 'reservado'])->default('libre');
            $table->foreignId('feriante_id')->nullable()->constrained('feriantes')->nullOnDelete();
        });
    }
};
