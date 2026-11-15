<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Los feriantes ya no necesitan aprobación del coordinador para quedar
     * habilitados: las cuentas que seguían pendientes pasan a aprobadas.
     */
    public function up(): void
    {
        DB::table('feriantes')
            ->where('estado', 'pendiente')
            ->update(['estado' => 'aprobado']);
    }

    public function down(): void
    {
        // No se puede distinguir cuáles estaban pendientes antes del cambio.
    }
};
