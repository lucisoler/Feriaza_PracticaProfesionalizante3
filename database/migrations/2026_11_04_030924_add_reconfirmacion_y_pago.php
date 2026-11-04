<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Reconfirmación del puesto 72 horas antes, medio y registro del pago, y
     * anulación de la cuenta del feriante (RN-15 a RN-19).
     */
    public function up(): void
    {
        Schema::table('eventos', function (Blueprint $table) {
            // Día y hora en que el coordinador cobra las mesas (RN-17)
            $table->date('pago_fecha')->nullable()->after('hora_inicio');
            $table->time('pago_hora')->nullable()->after('pago_fecha');
        });

        Schema::table('inscripciones', function (Blueprint $table) {
            $table->dateTime('reconfirmada_at')->nullable()->after('confirmacion');
            // Se elige al reconfirmar; el sistema solo lo registra, no cobra (RN-16)
            $table->enum('medio_pago', ['efectivo', 'debito'])->nullable()->after('reconfirmada_at');
            // Lo marca el coordinador cuando recibe el pago
            $table->dateTime('pagado_at')->nullable()->after('medio_pago');
        });

        Schema::table('feriantes', function (Blueprint $table) {
            $table->enum('estado', ['pendiente', 'aprobado', 'rechazado', 'anulado'])->default('pendiente')->change();
            $table->string('motivo_anulacion', 255)->nullable()->after('estado');
        });
    }

    public function down(): void
    {
        Schema::table('feriantes', function (Blueprint $table) {
            $table->dropColumn('motivo_anulacion');
        });

        Schema::table('feriantes', function (Blueprint $table) {
            $table->enum('estado', ['pendiente', 'aprobado', 'rechazado'])->default('pendiente')->change();
        });

        Schema::table('inscripciones', function (Blueprint $table) {
            $table->dropColumn(['reconfirmada_at', 'medio_pago', 'pagado_at']);
        });

        Schema::table('eventos', function (Blueprint $table) {
            $table->dropColumn(['pago_fecha', 'pago_hora']);
        });
    }
};
