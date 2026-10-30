<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * La inscripción pasa a ser por día: en un evento de varios días el
     * feriante elige qué días va y una mesa para cada uno (RF-07).
     */
    public function up(): void
    {
        Schema::table('inscripciones', function (Blueprint $table) {
            $table->date('fecha')->nullable()->after('puesto_id');
        });

        // Las inscripciones anteriores quedan en el primer día de su evento
        foreach (DB::table('eventos')->get(['id', 'fecha_inicio']) as $evento) {
            DB::table('inscripciones')->where('evento_id', $evento->id)
                ->update(['fecha' => $evento->fecha_inicio]);
        }

        Schema::table('inscripciones', function (Blueprint $table) {
            $table->date('fecha')->nullable(false)->change();
        });

        // Primero los índices nuevos (MySQL los necesita para la clave foránea de evento_id)
        Schema::table('inscripciones', function (Blueprint $table) {
            $table->unique(['evento_id', 'fecha', 'feriante_id']);
            $table->unique(['evento_id', 'fecha', 'puesto_id']);
        });

        Schema::table('inscripciones', function (Blueprint $table) {
            $table->dropUnique(['evento_id', 'feriante_id']);
            $table->dropUnique(['evento_id', 'puesto_id']);
        });
    }

    public function down(): void
    {
        Schema::table('inscripciones', function (Blueprint $table) {
            $table->unique(['evento_id', 'feriante_id']);
            $table->unique(['evento_id', 'puesto_id']);
        });

        Schema::table('inscripciones', function (Blueprint $table) {
            $table->dropUnique(['evento_id', 'fecha', 'feriante_id']);
            $table->dropUnique(['evento_id', 'fecha', 'puesto_id']);
        });

        Schema::table('inscripciones', function (Blueprint $table) {
            $table->dropColumn('fecha');
        });
    }
};
