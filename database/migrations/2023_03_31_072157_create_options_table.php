<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('options', function (Blueprint $table) {
            $table->id();
            $table->integer('shop_id');
            $table->string('title')->nullable();
            $table->tinyInteger('multiple_select')->default(0);
            $table->tinyInteger('enabled_price')->default(0);
            $table->tinyInteger('pre_select')->default(0);

            $table->string('en_title')->nullable();
            $table->string('fr_title')->nullable();
            $table->string('el_title')->nullable();
            $table->string('it_title')->nullable();
            $table->string('es_title')->nullable();
            $table->string('de_title')->nullable();
            $table->string('bg_title')->nullable();
            $table->string('tr_title')->nullable();
            $table->string('ro_title')->nullable();
            $table->string('sr_title')->nullable();
            $table->string('zh_title')->nullable();
            $table->string('ru_title')->nullable();
            $table->string('pl_title')->nullable();
            $table->string('ka_title')->nullable();
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
        Schema::dropIfExists('options');
    }
}
