<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdditionalLanguage extends Model
{
    use HasFactory;

    public function language()
    {
        return $this->hasOne(Languages::class,'id','language_id');
    }
}
