<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRepartoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reparto', function (Blueprint $table) {
            $table->bigIncrements('cod_reparto');
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
        Schema::dropIfExists('reparto');
    }
}
