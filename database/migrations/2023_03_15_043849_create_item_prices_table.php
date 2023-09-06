<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateItemPricesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('item_prices', function (Blueprint $table) {
            $table->id();
            $table->integer('item_id');
            $table->integer('shop_id');
            $table->double('price');
            $table->string('label')->nullable();
            $table->string('en_label')->nullable();
            $table->string('fr_label')->nullable();
            $table->string('el_label')->nullable();
            $table->string('it_label')->nullable();
            $table->string('es_label')->nullable();
            $table->string('de_label')->nullable();
            $table->string('bg_label')->nullable();
            $table->string('tr_label')->nullable();
            $table->string('ro_label')->nullable();
            $table->string('sr_label')->nullable();
            $table->string('zh_label')->nullable();
            $table->string('ru_label')->nullable();
            $table->string('pl_label')->nullable();
            $table->string('ka_label')->nullable();
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
        Schema::dropIfExists('item_prices');
    }
}
