<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tolerancia de 1 hora para presentarse y veto por inasistencia en un
     * evento grande (RN-07, RN-08).
     */
    public function up(): void
    {
        Schema::table('eventos', function (Blueprint $table) {
            // Lo tilda el coordinador: define si la inasistencia veta al feriante
            $table->boolean('grande')->default(false)->after('estado');
        });

        Schema::table('feriantes', function (Blueprint $table) {
            $table->foreignId('vetado_desde_evento_id')->nullable()->after('motivo_anulacion')->constrained('eventos')->nullOnDelete();
            $table->string('vetado_motivo', 255)->nullable()->after('vetado_desde_evento_id');
            $table->dateTime('vetado_at')->nullable()->after('vetado_motivo');
        });

        Schema::table('sanciones', function (Blueprint $table) {
            // Una sanción automática (vencida la tolerancia) no la registra nadie
            $table->foreignId('registrada_por')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('sanciones', function (Blueprint $table) {
            $table->foreignId('registrada_por')->nullable(false)->change();
        });

        Schema::table('feriantes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('vetado_desde_evento_id');
            $table->dropColumn(['vetado_motivo', 'vetado_at']);
        });

        Schema::table('eventos', function (Blueprint $table) {
            $table->dropColumn('grande');
        });
    }
};
