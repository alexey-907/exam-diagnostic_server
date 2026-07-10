<?php

namespace App\Services;

use App\Models\Student_session;
use App\Models\Subject_catalog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ScoringEngine
{
    // Когнитивные веса из ТЗ (Раздел 11)
    private $cognitiveWeights = [
        'ALG'     => ['LR' => 0.35, 'AR' => 0.25, 'WM' => 0.25, 'ATT' => 0.15],
        'GEO'     => ['SP' => 0.30, 'VM' => 0.25, 'LR' => 0.25, 'ATT' => 0.20],
        'PHYS'    => ['LR' => 0.30, 'AR' => 0.25, 'SP' => 0.25, 'WM' => 0.20],
        'INFO'    => ['LR' => 0.35, 'AR' => 0.30, 'WM' => 0.20, 'ATT' => 0.15],
        'CHEM'    => ['VM' => 0.30, 'LR' => 0.25, 'AR' => 0.25, 'ATT' => 0.20],
        'BIO'     => ['VM' => 0.35, 'VR' => 0.25, 'ATT' => 0.20, 'LR' => 0.20],
        'HIST'    => ['VM' => 0.40, 'VR' => 0.25, 'LR' => 0.20, 'ATT' => 0.15],
        'SOC'     => ['VR' => 0.30, 'LR' => 0.25, 'VM' => 0.25, 'ATT' => 0.20],
        'LIT'     => ['VR' => 0.35, 'VM' => 0.25, 'AR' => 0.20, 'ATT' => 0.20],
        'LANG'    => ['VM' => 0.35, 'VR' => 0.25, 'ATT' => 0.40],
        'RUS'     => ['VR' => 0.40, 'VM' => 0.30, 'ATT' => 0.30],
        'MATH_OGE'=> ['LR' => 0.35, 'AR' => 0.25, 'WM' => 0.25, 'ATT' => 0.15],
    ];

    public static function calculate(Student_session $session): array
    {
        $engine = new self();
        $sessionId = $session->id;
        $subjectScores = [];

        // Получаем все доступные предметы из каталога
        $subjects = DB::table('subject_catalogs')->get();

        Log::info('Найдено предметов в каталоге: ' . $subjects->count());

        foreach ($subjects as $subject) {
            $G = $engine->calcGradeIndex($sessionId, $subject->id);
            $A = $engine->calcAttitudeIndex($sessionId, $subject->code);
            $C = $engine->calcCognitiveFit($subject->code, $sessionId);
            $R = $engine->calcGoalRelevance($subject->code, $sessionId);
            $T = $engine->calcTimeFeasibility($session);
            $P = 50; // Заглушка мини-проб

            // Формулы расчета итогового веса предмета из ТЗ (Раздел 9)
            if ($session->exam_type === 'OGE') {
                $total = 0.22 * $G + 0.22 * $A + 0.18 * $C + 0.24 * $R + 0.10 * $T + 0.04 * $P;
            } else {
                $total = 0.18 * $G + 0.18 * $A + 0.16 * $C + 0.34 * $R + 0.10 * $T + 0.04 * $P;
            }

            $subjectScores[$subject->code] = [
                'title' => $subject->title,
                'gradeIndex' => $G,
                'total' => max(0, min(100, round($total)))
            ];
        }

        // =========================================================================
        // УМНЫЙ СИНТЕЗ ПРОФИЛЬНОЙ МАТЕМАТИКИ ДЛЯ ЕГЭ
        // =========================================================================
        if ($session->exam_type === 'EGE') {
            // Вычисляем среднюю школьную готовность по Алгебре и Геометрии
            $algData = $subjectScores['ALG'] ?? ['gradeIndex' => 50, 'total' => 50];
            $geoData = $subjectScores['GEO'] ?? ['gradeIndex' => 50, 'total' => 50];

            $gMath = ($algData['gradeIndex'] + $geoData['gradeIndex']) / 2;

            // Считаем когнитивное соответствие строго по коду MATH_PROF из вашего массива весов!
            $cMath = $engine->calcCognitiveFit('MATH_PROF', $sessionId);
            $rMath = $engine->calcGoalRelevance('MATH_PROF', $sessionId);
            $tMath = $engine->calcTimeFeasibility($session);

            // Вычисляем итоговый средний интерес к математике
            $aMath = ($engine->calcAttitudeIndex($sessionId, 'ALG') + $engine->calcAttitudeIndex($sessionId, 'GEO')) / 2;

            $totalMath = 0.18 * $gMath + 0.18 * $aMath + 0.16 * $cMath + 0.34 * $rMath + 0.10 * $tMath + 0.04 * 50;

            // Записываем синтезированную "Профильную математику" в массив результатов,
            // чтобы комбинаторный метод buildRecommendations смог её прочитать!
            $subjectScores['MATH_PROF'] = [
                'title' => 'Математика (профильная)',
                'gradeIndex' => $gMath,
                'total' => max(0, min(100, round($totalMath)))
            ];
        }

        Log::info('subjectScores посчитаны: ' . count($subjectScores));

        $recommendations = $engine->buildRecommendations($session, $subjectScores);

        Log::info('Рекомендаций сгенерировано: ' . count($recommendations));

        return $recommendations;
    }

    private function calcGradeIndex(int $sessionId, int $subjectId): float
    {
        $grade = DB::table('subject_grades')
            ->where('session_id', $sessionId)
            ->where('subject_id', $subjectId)
            ->first();

        if (!$grade) return 50;

        $mapping = [2 => 20, 3 => 50, 4 => 75, 5 => 95];
        $quarter = $mapping[$grade->quarter_grade] ?? 50;
        $annual = $mapping[$grade->annual_grade] ?? 50;

        return 0.4 * $quarter + 0.6 * $annual;
    }

    private function calcAttitudeIndex(int $sessionId, string $subjectCode): float
    {
        $avgScore = DB::table('subject_attitudes')
            ->where('session_id', $sessionId)
            ->where('subject_code', $subjectCode)
            ->avg('score');

        if (!$avgScore) return 50.0;
        return ($avgScore / 5) * 100;
    }

    private function calcCognitiveFit(string $subjectCode, int $sessionId): float
    {
        $weights = $this->cognitiveWeights[$subjectCode] ?? [];
        if (empty($weights)) return 50;

        $cognitiveResults = DB::table('cognitive_results')
            ->where('session_id', $sessionId)
            ->pluck('normalized_score', 'test_code')
            ->toArray();

        $score = 0;
        foreach ($weights as $factor => $weight) {
            $normScore = $cognitiveResults[$factor] ?? 50;
            $score += $weight * ($normScore / 100);
        }

        return $score * 100;
    }

    private function calcGoalRelevance(string $subjectCode, int $sessionId): float
    {
        // ОЖИВЛЕНИЕ ЗАГЛУШКИ: Сверяем предмет с выбранным на Шаге 12 кластером
        $userClusters = DB::table('goal_profiles')
            ->where('session_id', $sessionId)
            ->where('goal_type', 'PROFESSIONAL_CLUSTER')
            ->pluck('target_profession')
            ->toArray();

        if (empty($userClusters)) return 50;

        // Карта соответствия кодов предметов и кластеров интересов по ТЗ
        $matrix = [
            'IT' => ['INFO', 'ALG', 'GEO'],
            'ENGINEERING' => ['PHYS', 'ALG', 'GEO'],
            'MEDICINE' => ['CHEM', 'BIO'],
            'ECONOMICS' => ['ALG', 'SOC'],
            'HUMANITIES' => ['HIST', 'LIT', 'LANG']
        ];

        foreach ($userClusters as $cluster) {
            if (isset($matrix[$cluster]) && in_array($subjectCode, $matrix[$cluster])) {
                return 100.0; // Идеальная релевантность цели!
            }
        }

        return 50.0; // Нейтральная релевантность
    }

    private function calcTimeFeasibility($session): float
    {
        $now = date('Y');
        $targetYear = $session->target_year ?? ($now + 1);
        $monthsLeft = ($targetYear - $now) * 12;
        $monthsLeft += 6;

        if ($monthsLeft > 12) return 90;
        if ($monthsLeft > 9) return 75;
        if ($monthsLeft > 6) return 50;
        if ($monthsLeft > 3) return 30;
        return 15;
    }

    private function buildCandidateSets($session): array
    {
        $mandatory = ['RUS'];

        // 1. Настраиваем обязательные предметы в зависимости от типа экзамена
        if ($session->exam_type === 'OGE') {
            // Для ОГЭ добавляем Алгебру и Геометрию
            if (!in_array('ALG', $mandatory)) $mandatory[] = 'ALG';
            if (!in_array('GEO', $mandatory)) $mandatory[] = 'GEO';
        } else {
            // Для ЕГЭ и EARLY добавляем Профильную математику
            if (!in_array('MATH_PROF', $mandatory)) $mandatory[] = 'MATH_PROF';
        }

        // 2. УНИВЕРСАЛЬНЫЙ РЕЛЯЦИОННЫЙ ЗАПРОС (Для ОГЭ, ЕГЭ и EARLY одновременно!)
        $electives = DB::table('subject_catalogs')
            ->join('exam_subject_links', 'subject_catalogs.id', '=', 'exam_subject_links.subject_id')
            ->where('exam_subject_links.exam_type', $session->exam_type)
            ->where('subject_catalogs.mandatory_flag', false)
            ->pluck('subject_catalogs.code')
            ->toArray();

        // 3. Защита: Если элективов нет — возвращаем пустой массив
        if (empty($electives)) {
            Log::warning('Нет доступных предметов для выбора! exam_type: ' . $session->exam_type);
            return [];
        }

        // 4. Исключаем обязательные предметы (RUS, ALG, GEO, MATH_PROF) из перебора пар,
        // чтобы они не дублировались в наборах по выбору
        $excludeList = array_merge($mandatory, ['ALG', 'GEO', 'MATH_PROF']);
        $electives = array_values(array_diff($electives, $excludeList));

        // 5. Алгоритм комбинаторики (сборка пар дополнительных предметов)
        $sets = [];
        if (count($electives) >= 2) {
            for ($i = 0; $i < count($electives); $i++) {
                for ($j = $i + 1; $j < count($electives); $j++) {
                    $sets[] = array_merge($mandatory, [$electives[$i], $electives[$j]]);
                }
            }
        } else {
            $sets[] = array_merge($mandatory, $electives);
        }

        return $sets;
    }

    private function buildRecommendations($session, $subjectScores): array
    {
        $candidateSets = $this->buildCandidateSets($session);
        $rankedSets = [];

        // 1. Считаем время до экзамена
        $nowYear = date('Y');
        $targetYear = $session->target_year ?? ($nowYear + 1);
        $monthsLeft = ($targetYear - $nowYear) * 12;
        $currentMonth = date('n');
        $monthsLeft += (5 - $currentMonth);

        $timeStrategy = 'COMPORTABLE';
        if ($monthsLeft <= 9)  $timeStrategy = 'INTENSIVE';
        if ($monthsLeft <= 4)  $timeStrategy = 'CRITICAL';

        // Логируем для отладки
        Log::info('Начинаем ранжирование ' . count($candidateSets) . ' наборов-кандидатов.');

        foreach ($candidateSets as $set) {
            $totalScore = 0;
            $risks = [];
            $whyFits = [];
            $subjectTitles = [];

            foreach ($set as $code) {
                // ГЛАВНОЕ ИСПРАВЛЕНИЕ: Если предмета нет в расчете ученика (например, GEO или PHYS),
                // мы больше не возвращаем null! Мы берем дефолтные 50 баллов, чтобы не обнулять весь набор!
                if (isset($subjectScores[$code])) {
                    $score = $subjectScores[$code];
                } else {
                    $score = [
                        'title' => $this->getSubjectTitleByCode($code),
                        'gradeIndex' => 50,
                        'total' => 50
                    ];
                }

                $totalScore += $score['total'];
                $subjectTitles[] = $score['title'];

                if ($score['total'] > 70) {
                    $whyFits[] = "{$score['title']}: высокая готовность";
                }
                if (($score['gradeIndex'] ?? 50) < 55) {
                    $risks[] = "{$score['title']}: слабая школьная база";
                }
            }

            $avgScore = round($totalScore / count($set));
            $risk = $this->classifyRisk($risks);

            // Адаптивные часовые планы по подготовке
            $firstActions = [];
            $summaryText = '';

            if ($timeStrategy === 'CRITICAL') {
                $summaryText = 'Экстренный профиль! До экзамена осталось всего ' . max(1, $monthsLeft) . ' мес. Требуется немедленная мобилизация сил.';
                $firstActions = [
                    'Заниматься усердно: от 3-4 часов КАЖДЫЙ ДЕНЬ по предметам набора',
                    'Срочно нанять репетитора или записаться на экспресс-курсы штурма',
                    'Каждую неделю писать полный пробный тест для контроля скорости',
                    'Учить строго кодификатор ФИПИ, отбросив второстепенные темы'
                ];
            } elseif ($timeStrategy === 'INTENSIVE') {
                $summaryText = 'Интенсивный профиль. До экзамена менее года (' . $monthsLeft . ' мес.). Нужна регулярная плотная работа.';
                $firstActions = [
                    'Выделять по 1.5–2 часа 4 дня в неделю на самостоятельное решение задач',
                    'Пройти официальную демоверсию ФИПИ текущего года в ближайшие 3 дня',
                    'Составить жесткий чек-лист тем по кодификатору на каждый месяц учебного года',
                    'Сделать упор на предметы с пометкой "слабая школьная база"'
                ];
            } else {
                $summaryText = 'Долгосрочный профиль. У тебя в запасе много времени (' . $monthsLeft . ' мес.). Можно готовиться спокойно и глубоко.';
                $firstActions = [
                    'Заниматься в поддерживающем режиме: 2-3 часа в неделю на каждый предмет',
                    'Развивать когнитивные навыки и логику через профильные кружки или олимпиады',
                    'В текущем году спокойно закрыть все базовые пробелы по школьной программе',
                    'Начать углубленное изучение сложных тем ("Часть С") со следующего года'
                ];
            }

            // Добавляем набор в итоговый массив (Ключи set, score, track строго под ваш TS рендер!)
            $rankedSets[] = [
                'set' => $subjectTitles,
                'score' => $avgScore,
                'track' => $risk === 'low' ? 'Рекомендуемый профиль' : 'Альтернативный профиль',
                'riskLevel' => $risk,
                'explanation' => [
                    'summary' => $summaryText,
                    'why' => !empty($whyFits) ? $whyFits : ['Сбалансированное когнитивное соответствие'],
                    'risks' => !empty($risks) ? $risks : ['Риски не обнаружены'],
                    'firstActions' => $firstActions,
                    'disclaimer' => 'Рекомендация сформирована на основе весов ФИПИ.'
                ]
            ];
        }

        // Если по какой-то причине наборы не собрались (массив пустой), только тогда включаем fallback
        if (empty($rankedSets)) {
            Log::warning('Не удалось сформировать ранжированные наборы! Применяем экстренный фолбек.');
            return [
                [
                    'set' => ['Русский язык', 'Алгебра', 'Геометрия', 'Информатика'],
                    'score' => 70,
                    'track' => 'Базовый технический профиль',
                    'riskLevel' => 'low',
                    'explanation' => ['summary' => 'Внутренняя ошибка калькуляции весов.', 'why' => [], 'risks' => [], 'firstActions' => [], 'disclaimer' => '']
                ]
            ];
        }

        // Сортируем наборы по убыванию баллов
        usort($rankedSets, function($a, $b) {
            return $b['score'] <=> $a['score'];
        });

        Log::info('Успешно ранжировано наборов: ' . count($rankedSets));

        // Возвращаем ТОП-3 лучших набора
        return array_slice($rankedSets, 0, 1);
    }


    /**
     * Заглушка на случай, если не удалось сгенерировать рекомендации
     */
    private function getFallbackRecommendations($session): array
    {
        Log::info('  fallback dspdfy');

        $fallbackSets = [
            ['Русский язык', 'Математика', 'Информатика'],
            ['Русский язык', 'Математика', 'Физика'],
            ['Русский язык', 'Математика', 'Обществознание'],
        ];

        $result = [];
        foreach ($fallbackSets as $index => $set) {
            $result[] = [
                'set' => $set,
                'score' => 70 - ($index * 5),
                'track' => $index === 0 ? 'Рекомендуемый набор' : 'Альтернативный набор',
                'riskLevel' => $index === 0 ? 'low' : 'moderate',
                'explanation' => [
                    'summary' => 'Набор сформирован на основе доступных предметов. Для точного расчета заполните все данные.',
                    'why' => ['Базовый набор предметов для поступления'],
                    'risks' => ['Рекомендация основана на общих данных'],
                    'firstActions' => [
                        'Проверьте правильность заполнения оценок',
                        'Пройдите все когнитивные тесты полностью',
                        'Уточните цель поступления'
                    ],
                    'disclaimer' => 'Рекомендация является предварительной. Обратитесь к консультанту для уточнения.'
                ]
            ];
        }

        return $result;
    }

    private function getTrackName(string $risk, float $score): string
    {
        if ($risk === 'low' && $score >= 80) {
            return 'Оптимальный профиль';
        }
        if ($risk === 'low') {
            return 'Рекомендуемый профиль';
        }
        if ($risk === 'moderate') {
            return 'Альтернативный профиль';
        }
        if ($risk === 'high') {
            return 'Сложный профиль (требует усилий)';
        }
        return 'Экспериментальный профиль';
    }

    private function classifyRisk(array $risks): string
    {
        $count = count($risks);
        if ($count === 0) return 'low';
        if ($count <= 2) return 'moderate';
        if ($count <= 4) return 'high';
        return 'critical';
    }


}
