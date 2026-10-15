<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Equivalencia entre el rubro anterior y el nombre de la categoría. */
    private array $equivalencias = [
        'artesano'     => 'Artesanos',
        'manualidades' => 'Manualidades',
        'masas'        => 'Masas',
        'revendedor'   => 'Revendedores',
        'otro'         => 'Otro',
    ];

    public function up(): void
    {
        // 1) Nueva columna, por ahora opcional para poder completar los datos
        Schema::table('feriantes', function (Blueprint $table) {
            $table->foreignId('categoria_id')
                ->nullable()
                ->after('nombre_emprendimiento')
                ->constrained('categorias');
        });

        // 2) Convertir el rubro de los feriantes existentes en su categoría
        $idOtro = DB::table('categorias')->where('nombre', 'Otro')->value('id');

        foreach ($this->equivalencias as $rubro => $nombre) {
            $idCategoria = DB::table('categorias')->where('nombre', $nombre)->value('id');
            DB::table('feriantes')->where('rubro', $rubro)->update(['categoria_id' => $idCategoria]);
        }
        // Cualquier valor desconocido pasa a "Otro"
        DB::table('feriantes')->whereNull('categoria_id')->update(['categoria_id' => $idOtro]);

        // 3) Ya con datos completos: la categoría pasa a ser obligatoria y se quita el rubro
        Schema::table('feriantes', function (Blueprint $table) {
            $table->unsignedBigInteger('categoria_id')->nullable(false)->change();
            $table->dropColumn('rubro');
        });
    }

    public function down(): void
    {
        Schema::table('feriantes', function (Blueprint $table) {
            $table->string('rubro')->nullable()->after('nombre_emprendimiento');
        });

        foreach ($this->equivalencias as $rubro => $nombre) {
            $idCategoria = DB::table('categorias')->where('nombre', $nombre)->value('id');
            DB::table('feriantes')->where('categoria_id', $idCategoria)->update(['rubro' => $rubro]);
        }

        Schema::table('feriantes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('categoria_id');
        });
    }
};
