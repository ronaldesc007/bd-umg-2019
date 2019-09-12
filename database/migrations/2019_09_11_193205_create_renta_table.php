<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRentaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('renta', function (Blueprint $table) {
            $table->bigIncrements('cod_renta');
            
            $table->dateTime('fecha_renta');
            $table->dateTime('fecha_devolucion');
            $table->decimal('valor_renta', 9, 2);
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
        Schema::dropIfExists('renta');
    }
}
