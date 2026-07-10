<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdmissionTrack extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'admission_tracks';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'level',
        'direction_code',
        'direction_title',
        'subject_set_rules',
        'source_url',
        'source_checked_at',
        'valid_year',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'subject_set_rules' => 'array',
        'source_checked_at' => 'datetime',
        'valid_year' => 'integer',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'id',
        'source_url',
        'source_checked_at',
    ];

    /**
     * Level constants.
     */
    const LEVEL_SPO = 'SPO';  // Среднее профессиональное образование
    const LEVEL_VO = 'VO';    // Высшее образование

    /**
     * Get the level label.
     */
    public function getLevelLabelAttribute(): string
    {
        $labels = [
            self::LEVEL_SPO => 'СПО (колледж/техникум)',
            self::LEVEL_VO => 'ВО (вуз)',
        ];

        return $labels[$this->level] ?? $this->level;
    }

    /**
     * Get required subjects for this track.
     */
    public function getRequiredSubjectsAttribute(): array
    {
        $rules = $this->subject_set_rules;
        return $rules['required'] ?? [];
    }

    /**
     * Get optional subject combinations for this track.
     */
    public function getOptionalSubjectsAttribute(): array
    {
        $rules = $this->subject_set_rules;
        return $rules['oneOf'] ?? [];
    }

    /**
     * Get all possible subject combinations for this track.
     */
    public function getAllSubjectCombinations(): array
    {
        $rules = $this->subject_set_rules;
        $combinations = [];

        $required = $rules['required'] ?? [];
        $oneOf = $rules['oneOf'] ?? [];

        if (empty($oneOf)) {
            // Если нет опциональных предметов, возвращаем только обязательные
            return [$required];
        }

        // Для каждой комбинации опциональных предметов
        foreach ($oneOf as $optionGroup) {
            $combinations[] = array_merge($required, $optionGroup);
        }

        return $combinations;
    }

    /**
     * Check if this track requires a specific subject.
     */
    public function requiresSubject(string $subjectCode): bool
    {
        $required = $this->getRequiredSubjectsAttribute();
        return in_array($subjectCode, $required);
    }

    /**
     * Check if this track accepts a specific subject as optional.
     */
    public function acceptsSubject(string $subjectCode): bool
    {
        $optional = $this->getOptionalSubjectsAttribute();
        foreach ($optional as $group) {
            if (in_array($subjectCode, $group)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if a set of subjects matches this track.
     */
    public function matchesSubjectSet(array $subjectCodes): bool
    {
        $required = $this->getRequiredSubjectsAttribute();

        // Проверяем, что все обязательные предметы присутствуют
        foreach ($required as $requiredSubject) {
            if (!in_array($requiredSubject, $subjectCodes)) {
                return false;
            }
        }

        $optional = $this->getOptionalSubjectsAttribute();

        // Если нет опциональных предметов, набор подходит
        if (empty($optional)) {
            return true;
        }

        // Проверяем, что есть хотя бы одна комбинация опциональных предметов
        foreach ($optional as $optionGroup) {
            $matchCount = 0;
            foreach ($optionGroup as $optionalSubject) {
                if (in_array($optionalSubject, $subjectCodes)) {
                    $matchCount++;
                }
            }
            // Если все предметы из группы присутствуют
            if ($matchCount === count($optionGroup)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the relevance score for a subject (0-100).
     */
    public function getSubjectRelevance(string $subjectCode): int
    {
        if ($this->requiresSubject($subjectCode)) {
            return 100;
        }

        if ($this->acceptsSubject($subjectCode)) {
            return 80;
        }

        return 0;
    }

    /**
     * Scope a query to only include tracks for a specific year.
     */
    public function scopeForYear($query, int $year)
    {
        return $query->where('valid_year', $year);
    }

    /**
     * Scope a query to only include tracks for a specific level.
     */
    public function scopeForLevel($query, string $level)
    {
        return $query->where('level', $level);
    }

    /**
     * Scope a query to only include SPO tracks.
     */
    public function scopeSpo($query)
    {
        return $query->where('level', self::LEVEL_SPO);
    }

    /**
     * Scope a query to only include VO tracks.
     */
    public function scopeVo($query)
    {
        return $query->where('level', self::LEVEL_VO);
    }

    /**
     * Scope a query to only include tracks from a valid source.
     */
    public function scopeValidSource($query)
    {
        return $query->whereNotNull('source_url')
            ->where('source_checked_at', '>=', now()->subYear());
    }

    /**
     * Get the clusters/directions for a specific goal type.
     */
    public static function getClustersForGoal(string $goalType): array
    {
        $clusters = [
            'UNIVERSITY' => [
                'IT' => ['INFO', 'MATH_PROF', 'PHYS'],
                'Engineering' => ['MATH_PROF', 'PHYS', 'INFO'],
                'Medicine' => ['CHEM', 'BIO'],
                'Economics' => ['MATH_PROF', 'SOC'],
                'Law' => ['SOC', 'HIST'],
                'Humanities' => ['LIT', 'HIST', 'LANG_EN'],
                'Linguistics' => ['LANG_EN', 'LIT', 'HIST'],
            ],
            'COLLEGE' => [
                'IT' => ['INFO', 'MATH_OGE'],
                'Engineering' => ['PHYS', 'MATH_OGE'],
                'Medicine' => ['CHEM', 'BIO'],
                'Economics' => ['SOC', 'MATH_OGE'],
            ],
            'PROFILE_CLASS' => [
                'IT' => ['INFO', 'MATH_PROF'],
                'Engineering' => ['PHYS', 'MATH_PROF'],
                'Medicine' => ['CHEM', 'BIO'],
                'Humanities' => ['LIT', 'HIST'],
                'Linguistics' => ['LANG_EN', 'LIT'],
            ],
        ];

        return $clusters[$goalType] ?? [];
    }
}
