<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Para no mandar el correo de recordatorio más de una vez por inscripción.
     */
    public function up(): void
    {
        Schema::table('inscripciones', function (Blueprint $table) {
            $table->dateTime('recordatorio_reconfirmacion_enviado_at')->nullable()->after('pagado_at');
        });
    }

    public function down(): void
    {
        Schema::table('inscripciones', function (Blueprint $table) {
            $table->dropColumn('recordatorio_reconfirmacion_enviado_at');
        });
    }
};
