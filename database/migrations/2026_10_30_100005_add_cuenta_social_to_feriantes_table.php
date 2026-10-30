<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Cuentas creadas con Google o Facebook (RF-01). Esas cuentas no tienen
     * contraseña propia: por eso la columna pasa a admitir nulos.
     */
    public function up(): void
    {
        Schema::table('feriantes', function (Blueprint $table) {
            $table->string('password')->nullable()->change();
        });

        Schema::table('feriantes', function (Blueprint $table) {
            $table->string('proveedor', 20)->nullable()->after('password');
            $table->string('proveedor_id')->nullable()->after('proveedor');
            $table->unique(['proveedor', 'proveedor_id']);
        });
    }

    public function down(): void
    {
        Schema::table('feriantes', function (Blueprint $table) {
            $table->dropUnique(['proveedor', 'proveedor_id']);
        });

        Schema::table('feriantes', function (Blueprint $table) {
            $table->dropColumn(['proveedor', 'proveedor_id']);
            $table->string('password')->nullable(false)->change();
        });
    }
};
