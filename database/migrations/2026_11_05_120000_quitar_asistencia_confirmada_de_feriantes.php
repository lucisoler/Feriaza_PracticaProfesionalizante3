<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Campo muerto: ningún formulario lo escribe ni ninguna vista lo
     * muestra. La confirmación real vive en inscripciones.confirmacion
     * (issue #48, docs/modelo-de-datos.md sección 5).
     */
    public function up(): void
    {
        Schema::table('feriantes', function (Blueprint $table) {
            $table->dropColumn('asistencia_confirmada');
        });
    }

    public function down(): void
    {
        Schema::table('feriantes', function (Blueprint $table) {
            $table->boolean('asistencia_confirmada')->default(false);
        });
    }
};
