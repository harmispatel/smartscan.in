<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;


    // Relation with Order Items
    public function order_items()
    {
        return $this->hasMany(OrderItems::class,'order_id','id');
    }


    // Relation with Shop
    public function shop()
    {
        return $this->hasOne(Shop::class,'id','shop_id');
    }
}
