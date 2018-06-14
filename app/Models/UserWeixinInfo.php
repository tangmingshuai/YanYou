<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserWeixinInfo extends Model
{
    protected $fillable = [
        'user_id','nickname','avatar','introduction'
    ];

    protected $hidden = [
        'id','created_at', 'updated_at',
    ];

    public function getFillable()
    {
        return $this->fillable;
    }
}
