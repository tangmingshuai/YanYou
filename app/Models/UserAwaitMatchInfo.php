<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAwaitMatchInfo extends Model
{
    protected $fillable = [
        'user1_id','user2_id','share_url', 'state','expired_at'
    ];

    protected $hidden = [
        'id','created_at', 'updated_at',
    ];

    public function getFillable()
    {
        return $this->fillable;
    }
}
