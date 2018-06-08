<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserTargetInfo extends Model
{
    protected $fillable = [
        'user_id','name','phone', 'sex',
        'hometown', 'area','school_place','school_name','school_field','school_type','study_style','good_subject'
    ];
}
