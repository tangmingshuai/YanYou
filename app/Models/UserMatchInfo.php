<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserMatchInfo extends Model
{
    protected $fillable = [
        'user1_id','user2_id'
    ];

    protected $hidden = [
        'id','created_at', 'updated_at',
    ];

    public function getFillable()
    {
        return $this->fillable;
    }
}
