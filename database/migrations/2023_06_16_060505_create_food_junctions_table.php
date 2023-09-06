<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFoodJunctionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('food_junctions', function (Blueprint $table) {
            $table->id();
            $table->string('junction_name');
            $table->string('junction_slug');
            $table->text('junction_description')->nullable();
            $table->string('junction_qr');
            $table->string('logo')->nullable();
            $table->string('shop_ids');
            $table->tinyInteger('status')->default(1);
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
        Schema::dropIfExists('food_junctions');
    }
}
