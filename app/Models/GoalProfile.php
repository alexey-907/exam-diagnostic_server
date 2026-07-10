<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class GoalProfile extends Model
{
    use HasUuids;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'goal_profiles';

    /**
     * Indicates if the model's ID is auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The data type of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'session_id',
        'goal_type',
        'target_profession',
        'target_program',
        'target_city',
        'priority_type',
        'cluster_code',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'session_id' => 'integer',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'id',
        'session_id',
        'created_at',
        'updated_at',
    ];

    /**
     * Goal type constants.
     */
    const GOAL_UNIVERSITY = 'UNIVERSITY';
    const GOAL_COLLEGE = 'COLLEGE';
    const GOAL_PROFILE_CLASS = 'PROFILE_CLASS';
    const GOAL_UNKNOWN = 'UNKNOWN';

    /**
     * Priority type constants.
     */
    const PRIORITY_MAX_SCORE = 'max_score';
    const PRIORITY_ADMISSION = 'admission';
    const PRIORITY_SAVE_CHOICE = 'save_choice';
    const PRIORITY_REDUCE_LOAD = 'reduce_load';
    const PRIORITY_UNDERSTAND_INTEREST = 'understand_interest';
    const PRIORITY_BALANCED = 'balanced';

    /**
     * Get the session that owns the goal profile.
     */
    public function session()
    {
        return $this->belongsTo(StudentSession::class, 'session_id');
    }

    /**
     * Get the goal type label.
     */
    public function getGoalTypeLabelAttribute(): string
    {
        $labels = [
            self::GOAL_UNIVERSITY => 'Поступление в ВУЗ',
            self::GOAL_COLLEGE => 'Поступление в колледж',
            self::GOAL_PROFILE_CLASS => 'Переход в профильный класс',
            self::GOAL_UNKNOWN => 'Пока не знаю',
        ];

        return $labels[$this->goal_type] ?? $this->goal_type;
    }

    /**
     * Get the priority type label.
     */
    public function getPriorityLabelAttribute(): string
    {
        $labels = [
            self::PRIORITY_MAX_SCORE => 'Максимальный балл',
            self::PRIORITY_ADMISSION => 'Поступление',
            self::PRIORITY_SAVE_CHOICE => 'Сохранить выбор',
            self::PRIORITY_REDUCE_LOAD => 'Снизить нагрузку',
            self::PRIORITY_UNDERSTAND_INTEREST => 'Понять интерес',
            self::PRIORITY_BALANCED => 'Сбалансированный подход',
        ];

        return $labels[$this->priority_type] ?? $this->priority_type;
    }

    /**
     * Check if goal is university-related.
     */
    public function isUniversity(): bool
    {
        return $this->goal_type === self::GOAL_UNIVERSITY;
    }

    /**
     * Check if goal is college-related.
     */
    public function isCollege(): bool
    {
        return $this->goal_type === self::GOAL_COLLEGE;
    }

    /**
     * Check if goal is profile class.
     */
    public function isProfileClass(): bool
    {
        return $this->goal_type === self::GOAL_PROFILE_CLASS;
    }

    /**
     * Check if goal is unknown.
     */
    public function isUnknown(): bool
    {
        return $this->goal_type === self::GOAL_UNKNOWN;
    }

    /**
     * Scope a query to only include university goals.
     */
    public function scopeUniversity($query)
    {
        return $query->where('goal_type', self::GOAL_UNIVERSITY);
    }

    /**
     * Scope a query to only include college goals.
     */
    public function scopeCollege($query)
    {
        return $query->where('goal_type', self::GOAL_COLLEGE);
    }

    /**
     * Scope a query to only include profile class goals.
     */
    public function scopeProfileClass($query)
    {
        return $query->where('goal_type', self::GOAL_PROFILE_CLASS);
    }
}
