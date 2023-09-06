<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ItemReview extends Model
{
    use HasFactory;

    // public function item()
    // {
    //     return $this->hasOne(Items::class,'id','item_id');
    // }

    public function item()
    {
        return $this->belongsTo(Items::class);
    }
}
