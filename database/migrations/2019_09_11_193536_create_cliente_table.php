<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateClienteTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cliente', function (Blueprint $table) {
            $table->bigIncrements('no_membresia');
            $table->string('nombre', 45);
            $table->string('apellido', 45);
            $table->string('direccion', 45);
            $table->string('telefono', 45);
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
        Schema::dropIfExists('cliente');
    }
}
