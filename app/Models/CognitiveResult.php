<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class CognitiveResult extends Model
{
    public $timestamps = false;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'cognitive_results';

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
        'test_code',
        'raw_score',
        'normalized_score',
        'level_code',
        'interpretation',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'raw_score' => 'float',
        'normalized_score' => 'integer',
        'session_id' => 'integer',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */

    /**
     * Get the session that owns the cognitive result.
     */
    public function session()
    {
        return $this->belongsTo(StudentSession::class, 'session_id');
    }

    /**
     * Get the level label based on normalized score.
     */
    public function getLevelLabelAttribute(): string
    {
        $levels = [
            'low' => 'Зона риска',
            'medium' => 'Средний уровень',
            'high' => 'Сильная сторона',
        ];

        return $levels[$this->level_code] ?? 'Не определено';
    }

    /**
     * Get the test name based on test code.
     */
    public function getTestNameAttribute(): string
    {
        $tests = [
            'WM' => 'Рабочая память',
            'VM' => 'Вербальная память',
            'LR' => 'Логическое мышление',
            'AR' => 'Абстрактно-символическое мышление',
            'VR' => 'Вербальное понимание',
            'SP' => 'Пространственное мышление',
            'ATT' => 'Внимание и саморегуляция',
            'SELF_REG' => 'Саморегуляция',
        ];

        return $tests[$this->test_code] ?? $this->test_code;
    }

    /**
     * Scope a query to only include results for a specific test.
     */
    public function scopeForTest($query, string $testCode)
    {
        return $query->where('test_code', $testCode);
    }

    /**
     * Scope a query to only include high-level results.
     */
    public function scopeHigh($query)
    {
        return $query->where('level_code', 'high');
    }

    /**
     * Scope a query to only include low-level results (risk zone).
     */
    public function scopeLow($query)
    {
        return $query->where('level_code', 'low');
    }
}
