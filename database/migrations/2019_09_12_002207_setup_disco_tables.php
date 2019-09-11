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
            $table->foreign('pelicula_cod_pelicula')->references('cod_pelicula')->on('pelicula')->onDelete('cascade');;
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

    }
}
