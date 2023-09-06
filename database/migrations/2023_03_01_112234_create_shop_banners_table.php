<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShopBannersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shop_banners', function (Blueprint $table) {
            $table->id();
            $table->integer('shop_id');
            $table->string('key');
            $table->string('display')->nullable();
            $table->string('background_color')->nullable();

            $table->longText('description')->nullable();
            $table->longText('en_description')->nullable();
            $table->longText('fr_description')->nullable();
            $table->longText('el_description')->nullable();
            $table->longText('it_description')->nullable();
            $table->longText('es_description')->nullable();
            $table->longText('de_description')->nullable();
            $table->longText('bg_description')->nullable();
            $table->longText('tr_description')->nullable();
            $table->longText('ro_description')->nullable();
            $table->longText('sr_description')->nullable();
            $table->longText('zh_description')->nullable();
            $table->longText('ru_description')->nullable();
            $table->longText('pl_description')->nullable();
            $table->longText('ka_description')->nullable();

            $table->string('image')->nullable();
            $table->string('en_image')->nullable();
            $table->string('fr_image')->nullable();
            $table->string('el_image')->nullable();
            $table->string('it_image')->nullable();
            $table->string('es_image')->nullable();
            $table->string('de_image')->nullable();
            $table->string('bg_image')->nullable();
            $table->string('tr_image')->nullable();
            $table->string('ro_image')->nullable();
            $table->string('sr_image')->nullable();
            $table->string('zh_image')->nullable();
            $table->string('ru_image')->nullable();
            $table->string('pl_image')->nullable();
            $table->string('ka_image')->nullable();
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
        Schema::dropIfExists('shop_banners');
    }
}
