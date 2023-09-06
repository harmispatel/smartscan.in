<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemsVisit extends Model
{
    use HasFactory;

    public function item()
    {
        return $this->hasOne(Items::class,'id','item_id');
    }
}
