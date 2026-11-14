<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Desanulación automática de cuentas (después de N eventos) y número de
     * inscripción por día de evento.
     */
    public function up(): void
    {
        Schema::table('feriantes', function (Blueprint $table) {
            // Desde cuándo está anulada la cuenta: cuenta los eventos que faltan para desanularla
            $table->dateTime('anulado_at')->nullable()->after('motivo_anulacion');
        });

        // Las cuentas ya anuladas se cuentan desde la última vez que se modificaron
        DB::table('feriantes')->where('estado', 'anulado')->update(['anulado_at' => DB::raw('updated_at')]);

        Schema::table('inscripciones', function (Blueprint $table) {
            // Orden de llegada de la inscripción en ese día del evento (1, 2, 3…)
            $table->unsignedSmallInteger('numero')->nullable()->after('puesto_id');
        });
    }

    public function down(): void
    {
        Schema::table('inscripciones', function (Blueprint $table) {
            $table->dropColumn('numero');
        });

        Schema::table('feriantes', function (Blueprint $table) {
            $table->dropColumn('anulado_at');
        });
    }
};
