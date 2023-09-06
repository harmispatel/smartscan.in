<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsersSubscriptions extends Model
{
    use HasFactory;

    public function subscription()
    {
        return $this->hasOne(Subscriptions::class,'id','subscription_id');
    }

    public function user()
    {
        return $this->hasOne(User::class,'id','user_id');
    }
}
