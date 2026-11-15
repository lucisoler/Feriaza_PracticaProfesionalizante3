<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Número de registro del feriante: se asigna automáticamente al darse de
     * alta, es único y no cambia aunque se editen sus datos.
     */
    public function up(): void
    {
        Schema::table('feriantes', function (Blueprint $table) {
            $table->unsignedInteger('numero_registro')->nullable()->after('id');
        });

        // Los feriantes que ya existen conservan el orden de alta (id)
        $ids = DB::table('feriantes')->orderBy('id')->pluck('id');
        foreach ($ids as $indice => $id) {
            DB::table('feriantes')->where('id', $id)->update(['numero_registro' => $indice + 1]);
        }

        Schema::table('feriantes', function (Blueprint $table) {
            $table->unique('numero_registro');
        });
    }

    public function down(): void
    {
        Schema::table('feriantes', function (Blueprint $table) {
            $table->dropUnique(['numero_registro']);
            $table->dropColumn('numero_registro');
        });
    }
};
