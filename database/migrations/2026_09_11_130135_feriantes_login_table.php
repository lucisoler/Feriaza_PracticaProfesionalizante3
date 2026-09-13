<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        schema::table('feriantes',function(Blueprint $table){
            $table->string('email')->unique()->after('apellido');
            $table->string('password')->after('email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        schema::table('feriantes',function(Blueprint $table){
        $table->dropColumn(['email','password']);
        });
    }
};
