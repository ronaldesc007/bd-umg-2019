<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SetupDiscoTables extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('disco', function (Blueprint $table) {
            $table->unsignedBigInteger('pelicula_cod_pelicula');
            $table->foreign('pelicula_cod_pelicula')->references('cod_pelicula')->on('pelicula')->onDelete('cascade');
        });
        
        Schema::table('reparto', function (Blueprint $table) {
            $table->unsignedBigInteger('pelicula_cod_pelicula');
            $table->unsignedBigInteger('actor_cod_actor');
            $table->foreign('pelicula_cod_pelicula')->references('cod_pelicula')->on('pelicula')->onDelete('cascade');
            $table->foreign('actor_cod_actor')->references('cod_actor')->on('actor')->onDelete('cascade');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('disco', function (Blueprint $table) {
            $table->dropForeign('disco_pelicula_cod_pelicula_foreign');
        });
        
        Schema::table('reparto', function (Blueprint $table) {
            $table->dropForeign('reparto_pelicula_cod_pelicula_foreign');
            $table->dropForeign('reparto_actor_cod_actor_foreign');
        });

    }
}
