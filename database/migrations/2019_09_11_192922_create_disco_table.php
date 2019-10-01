<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDiscoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('disco', function (Blueprint $table) {
            $table->bigIncrements('cod_disco');
            $table->integer('no_copias')->unsigned();
            //$table->integer('cod_pelicula')->unsigned();
            $table->string('formato', 45);
            //$table->integer('pelicula_cod_pelicula')->unsigned();
            $table->tinyinteger('isDeleted')->default(0);
            $table->tinyinteger('isSynced')->default(0);
            $table->tinyinteger('isUpdated')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('disco');
    }
}
