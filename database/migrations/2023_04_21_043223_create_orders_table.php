<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->integer('shop_id');
            $table->string('ip_address',50);
            $table->tinyInteger('is_new')->default(0);
            $table->string('firstname',30)->nullable();
            $table->string('lastname',30)->nullable();
            $table->string('email')->nullable();
            $table->string('phone',20)->nullable();
            $table->string('checkout_type',20);
            $table->string('payment_method',30);
            $table->longText('address')->nullable();
            $table->string('cgst')->nullable();
            $table->string('sgst')->nullable();
            $table->string('gst_amount')->nullable();
            $table->string('currency')->nullable();
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            $table->string('floor')->nullable();
            $table->string('door_bell')->nullable();
            $table->text('instructions')->nullable();
            $table->string('delivery_time')->nullable();
            $table->string('table',50)->nullable();
            $table->string('room',50)->nullable();
            $table->string('order_status');
            $table->text('reject_reason')->nullable();
            $table->string('estimated_time')->nullable();
            $table->string('total_qty')->nullable();
            $table->string('discount_per')->nullable();
            $table->string('discount_type')->nullable();
            $table->string('discount_value')->nullable();
            $table->string('order_subtotal')->nullable();
            $table->string('order_total')->nullable();
            $table->string('order_total_text')->nullable();
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
        Schema::dropIfExists('orders');
    }
}
