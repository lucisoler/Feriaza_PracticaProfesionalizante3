<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Agrega "habilitado" para poder retirar una mesa del plano sin borrarla (D-06).
     * Las columnas "estado" y "feriante_id" se mantienen por ahora: las usa el flujo
     * actual de inscripción. Se quitarán cuando la inscripción pase a hacerse por evento.
     */
    public function up(): void
    {
        Schema::table('puestos', function (Blueprint $table) {
            $table->boolean('habilitado')->default(true)->after('numero');
        });
    }

    public function down(): void
    {
        Schema::table('puestos', function (Blueprint $table) {
            $table->dropColumn('habilitado');
        });
    }
};
