<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePeliculaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pelicula', function (Blueprint $table) {
            $table->bigIncrements('cod_pelicula');
            $table->string('titulo', 45);
            $table->string('categoria', 45);
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
        Schema::dropIfExists('pelicula');
    }
}
