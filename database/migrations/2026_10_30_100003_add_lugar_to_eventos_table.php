<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** El coordinador elige en qué lugar se arma cada evento (RF-05). */
    public function up(): void
    {
        Schema::table('eventos', function (Blueprint $table) {
            $table->foreignId('lugar_id')->nullable()->after('nombre')->constrained('lugares');
        });

        // Los eventos cargados antes se hacían en la plaza
        $plaza = DB::table('lugares')->where('slug', 'plaza-central')->value('id');
        DB::table('eventos')->whereNull('lugar_id')->update(['lugar_id' => $plaza]);
    }

    public function down(): void
    {
        Schema::table('eventos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('lugar_id');
        });
    }
};
