<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserShop extends Model
{
    use HasFactory;

    public function shop()
    {
        return $this->hasOne(Shop::class,'id','shop_id');
    }

    public function user()
    {
        return $this->hasOne(User::class,'id','user_id');
    }
}
