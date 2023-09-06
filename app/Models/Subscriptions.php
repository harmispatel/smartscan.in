<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscriptions extends Model
{
    use HasFactory;

    public function  user_subscriptions()
    {
        return $this->hasMany(UsersSubscriptions::class,'subscription_id','id');
    }
}
