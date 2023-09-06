<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->integer('shop_id')->nullable();
            $table->string('category_type')->nullable();
            $table->tinyInteger('parent_category')->default(0);
            $table->integer('parent_id')->nullable();
            $table->longText('styles')->nullable();
            $table->string('name')->nullable();
            $table->tinyInteger('schedule')->default(0);
            $table->string('schedule_type')->nullable();
            $table->date('sch_start_date')->nullable();
            $table->date('sch_end_date')->nullable();
            $table->longText('schedule_value')->nullable();
            $table->text('description')->nullable();
            $table->integer('order_key')->nullable();
            $table->string('link_url')->nullable();
            $table->string('file')->nullable();
            $table->string('cover')->nullable();
            $table->tinyInteger('published')->default(0);

            $table->string('en_name')->nullable();
            $table->text('en_description')->nullable();
            $table->string('fr_name')->nullable();
            $table->text('fr_description')->nullable();
            $table->string('el_name')->nullable();
            $table->text('el_description')->nullable();
            $table->string('it_name')->nullable();
            $table->text('it_description')->nullable();
            $table->string('es_name')->nullable();
            $table->text('es_description')->nullable();
            $table->string('de_name')->nullable();
            $table->text('de_description')->nullable();
            $table->string('bg_name')->nullable();
            $table->text('bg_description')->nullable();
            $table->string('tr_name')->nullable();
            $table->text('tr_description')->nullable();
            $table->string('ro_name')->nullable();
            $table->text('ro_description')->nullable();
            $table->string('sr_name')->nullable();
            $table->text('sr_description')->nullable();
            $table->string('zh_name')->nullable();
            $table->text('zh_description')->nullable();
            $table->string('ru_name')->nullable();
            $table->text('ru_description')->nullable();
            $table->string('pl_name')->nullable();
            $table->text('pl_description')->nullable();
            $table->string('ka_name')->nullable();
            $table->text('ka_description')->nullable();
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
        Schema::dropIfExists('categories');
    }
}
