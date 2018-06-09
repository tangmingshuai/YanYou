<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserTargetInfo extends Model
{
    protected $fillable = [
        'user_id','sex',
        'hometown', 'area','school_place','school_name','school_field','school_type','study_style','good_subject'
    ];

    public function getFillable()
    {
        return $this->fillable;
    }
}
