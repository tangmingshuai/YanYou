<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class UserSignDetailInfo extends Model
{
    protected $fillable = [
        'user_id', 'day_timestamp', 'sign_timestamp'
    ];

    protected $hidden = [
        'id', 'created_at', 'updated_at'
    ];

    public function getFillable()
    {
        return $this->fillable;
    }
}
