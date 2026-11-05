<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * El coordinador no cobra la mesa por la app: se saca todo rastro de pago
     * (método de pago y día/hora de pago).
     */
    public function up(): void
    {
        Schema::table('eventos', function (Blueprint $table) {
            $table->dropColumn(['pago_fecha', 'pago_hora']);
        });

        Schema::table('inscripciones', function (Blueprint $table) {
            $table->dropColumn(['medio_pago', 'pagado_at']);
        });
    }

    public function down(): void
    {
        Schema::table('eventos', function (Blueprint $table) {
            $table->date('pago_fecha')->nullable()->after('hora_inicio');
            $table->time('pago_hora')->nullable()->after('pago_fecha');
        });

        Schema::table('inscripciones', function (Blueprint $table) {
            $table->enum('medio_pago', ['efectivo', 'debito'])->nullable()->after('reconfirmada_at');
            $table->dateTime('pagado_at')->nullable()->after('medio_pago');
        });
    }
};
