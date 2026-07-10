<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subject_grade extends Model
{

    protected $fillable = [
        'session_id',
        'quarter_grade',
        'annual_grade',
        'self_level',
        'subject_code',
        'attitude_json',
    ];
}
