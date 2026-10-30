<?php

use App\Models\Seccion;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Cada mesa pasa a pertenecer a un lugar y a una sección, con su posición
     * en el plano. El número deja de ser único global: se repite entre lugares.
     */
    public function up(): void
    {
        Schema::table('puestos', function (Blueprint $table) {
            $table->foreignId('lugar_id')->nullable()->after('id')->constrained('lugares')->cascadeOnDelete();
            $table->foreignId('seccion_id')->nullable()->after('lugar_id')->constrained('secciones')->nullOnDelete();
            $table->unsignedSmallInteger('x')->nullable()->after('habilitado');
            $table->unsignedSmallInteger('y')->nullable()->after('x');
        });

        // Primero el índice nuevo y después se quita el viejo
        Schema::table('puestos', function (Blueprint $table) {
            $table->unique(['lugar_id', 'numero']);
        });
        Schema::table('puestos', function (Blueprint $table) {
            $table->dropUnique(['numero']);
        });

        // Las mesas que ya existían se ubican en la primera sección con categoría de la Plaza Central
        $plaza = DB::table('lugares')->where('slug', 'plaza-central')->value('id');
        $seccion = Seccion::where('lugar_id', $plaza)->where('disponible', true)
            ->whereNotNull('categoria_id')->orderBy('id')->first();
        $existentes = DB::table('puestos')->orderBy('numero')->pluck('id');

        if ($seccion && $existentes->isNotEmpty()) {
            $posiciones = $seccion->posicionesLibres($existentes->count());
            foreach ($existentes as $i => $id) {
                $punto = $posiciones[$i] ?? null;
                DB::table('puestos')->where('id', $id)->update([
                    'lugar_id' => $plaza,
                    'seccion_id' => $punto ? $seccion->id : null,
                    'x' => $punto['x'] ?? null,
                    'y' => $punto['y'] ?? null,
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('puestos', function (Blueprint $table) {
            $table->unique('numero');
        });
        Schema::table('puestos', function (Blueprint $table) {
            $table->dropUnique(['lugar_id', 'numero']);
        });
        Schema::table('puestos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('seccion_id');
            $table->dropConstrainedForeignId('lugar_id');
            $table->dropColumn(['x', 'y']);
        });
    }
};
