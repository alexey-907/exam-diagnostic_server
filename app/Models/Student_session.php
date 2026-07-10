<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student_session extends Model
{
    //

    protected $fillable = [
        'role',
        'grade',
        'exam_type',
        'region',
        'target_track',
        'target_year',
        'subjects_data',
        'cognitive_results',
        'interests_data',
        'status',
        'user_id'
    ];

    protected $casts = [
        'subjects_data' => 'array',
        'cognitive_results' => 'array',
        'interests_data' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(Role::class, 'user_id');
    }

    public function grades()
    {
        return $this->hasMany(SubjectGrade::class, 'session_id');
    }

    public function cognitiveResults()
    {
        return $this->hasMany(CognitiveResult::class, 'session_id');
    }

    public function recommendations()
    {
        return $this->hasMany(SubjectSetRecommendation::class, 'session_id');
    }

}
