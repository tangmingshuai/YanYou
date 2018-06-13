<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserSignInfo extends Model
{
    protected $fillable = [
        'user_id', 'sign_day', 'sign_score'
    ];

    protected $hidden = [
        'id', 'created_at', 'updated_at'
    ];

    public function getFillable()
    {
        return $this->fillable;
    }
}
