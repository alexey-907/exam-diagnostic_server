<?php
//
//namespace App\Services;
//
//use App\Models\Student_session;
//use Illuminate\Support\Facades\DB;
//use Illuminate\Support0\Facades\Log;
//
//class ScoringEngine
//{
//    /**
//     * Главный метод расчета. Принимает созданную сессию студента.
//     */
//    public static function calculate(Student_session $session): array
//    {
//        $sessionId = $session->id;
//
//        // 1. ПОДТЯГИВАЕМ КОГНИТИВНЫЕ БАЛЛЫ ИЗ ТАБЛИЦЫ cognitive_results
//        // Смерджим результаты в удобный ассоциативный массив ['WM' => 85, 'LR' => 70...]
//        $cognitiveRows = DB::table('cognitive_results')
//            ->where('session_id', $sessionId)
//            ->pluck('raw_score', 'test_code')
//            ->toArray();
//
//        $wm  = $cognitiveRows['WM'] ?? 50;  // Рабочая память
//        $vm  = $cognitiveRows['VM'] ?? 50;  // Вербальная память
//        $lr  = $cognitiveRows['LR'] ?? 50;  // Логика
//        $ar  = $cognitiveRows['AR'] ?? 50;  // Абстракция
//        $vr  = $cognitiveRows['VR'] ?? 50;  // Вербальное понимание
//        $att = $cognitiveRows['ATT'] ?? 50; // Саморегуляция
//
//        // 2. ПОДТЯГИВАЕМ ВЫБРАННЫЕ ПРОФЕССИОНАЛЬНЫЕ КЛАСТЕРЫ
//        // Если у вас есть таблица goal_profiles, берем оттуда, иначе берем пустой массив
//        $interests = [];
//        if (Schema::hasTable('goal_profiles')) {
//            $interests = DB::table('goal_profiles')
//                ->where('session_id', $sessionId)
//                ->pluck('cluster_code')
//                ->toArray();
//        }
//
//        // 3. ПОДТЯГИВАЕМ И ОБРАБАТЫВАЕМ ОЦЕНКИ ИЗ ТАБЛИЦЫ subject_grades
//        $gradeRows = DB::table('subject_grades')
//            ->where('session_id', $sessionId)
//            ->get();
//
//        $calculatedSubjects = [];
//
//        foreach ($gradeRows as $row) {
//            $code = $row->subject_code;
//            $quarter = $row->quarter_grade ?? 3;
//            $annual = $row->annual_grade ?? 3;
//
//            // Извлекаем ответы анкеты S4 из созданной нами текстовой/JSON колонки
//            $attitudeAnswers = json_decode($row->attitude_json, true) ?? [];
//
//            // --- МАТЕМАТИКА 1. РАСЧЕТ ИНДЕКСА УСПЕВАЕМОСТИ (Grade Index) ---
//            $gradeIndex = self::calculateGradeIndex($quarter, $annual);
//
//            // --- МАТЕМАТИКА 2. РАСЧЕТ ИНДЕКСА ОТНОШЕНИЯ (Attitude Index) ---
//            // По ТЗ собираем ответы на вопросы: например, ID вопросов 1 и 2
//            $interest = $attitudeAnswers['1'] ?? 3; // Интерес (шкала 1-5)
//            $ease = $attitudeAnswers['2'] ?? 3;     // Легкость (шкала 1-5)
//
//            // Переводим шкалу 1-5 в 100-балльную
//            $attitudeIndex = (($interest + $ease) / 10) * 100;
//
//            // --- МАТЕМАТИКА 3. ИНТЕГРАЦИЯ КОГНИТИВНЫХ ФАКТОРОВ И ВЕСОВ ---
//            $cognitiveFit = 50; // Базовый уровень
//
//            // Точные науки (Физика, Информатика, Алгебра, Геометрия)
//            if (in_array($code, ['PHYS', 'INFO', 'ALG', 'GEO'])) {
//                $cognitiveFit = ($lr * 0.4) + ($ar * 0.4) + ($wm * 0.2);
//            }
//            // Гуманитарные науки (Русский, Литература, История, Обществознание, Иностранный язык)
//            elseif (in_array($code, ['RUS', 'LIT', 'HIST', 'SOC', 'LANG'])) {
//                $cognitiveFit = ($vm * 0.4) + ($vr * 0.4) + ($att * 0.2);
//            }
//            // Естественные науки (Биология, Химия, География)
//            elseif (in_array($code, ['BIO', 'CHEM', 'GEOGR'])) {
//                $cognitiveFit = ($wm * 0.3) + ($vm * 0.3) + ($lr * 0.4);
//            }
//
//            // --- МАТЕМАТИКА 4. ИТОГОВЫЙ СКОРИНГ ПРЕДМЕТА (0-100) ---
//            // Формула весов из ТЗ: 40% успеваемость, 30% отношение, 30% когнитивка
//            $subjectFinalScore = ($gradeIndex * 0.4) + ($attitudeIndex * 0.3) + ($cognitiveFit * 0.3);
//
//            // --- МАТЕМАТИКА 5. БОНУС КЛАСТЕРОВ ИНТЕРЕСОВ ---
//            $subjectFinalScore = self::applyClusterBonus($code, $interests, $subjectFinalScore);
//
//            // Жестко фиксируем границы математики
//            $subjectFinalScore = max(0, min(100, round($subjectFinalScore)));
//
//            $calculatedSubjects[$code] = [
//                'score' => $subjectFinalScore,
//                'title' => self::getSubjectTitleByCode($code)
//            ];
//        }
//
//        // --- 4. ФОРМИРОВАНИЕ И СОРТИРОВКА ТОП-3 НАБОРОВ ПРЕДМЕТОВ ---
//        return self::generateTopSets($calculatedSubjects, $interests);
//    }
//
//    private static function calculateGradeIndex($quarter, $annual): float
//    {
//        $avg = ($quarter + $annual) / 2;
//        if ($avg >= 4.8) return 100;
//        if ($avg >= 4.5) return 90;
//        if ($avg >= 4.0) return 80;
//        if ($avg >= 3.5) return 65;
//        if ($avg >= 3.0) return 50;
//        return 30;
//    }
//
//    private static function applyClusterBonus(string $code, array $interests, float $score): float
//    {
//        if (in_array('IT', $interests) && in_array($code, ['INFO', 'ALG', 'GEO'])) $score += 15;
//        if (in_array('ENGINEERING', $interests) && in_array($code, ['PHYS', 'ALG', 'GEO'])) $score += 15;
//        if (in_array('MEDICINE', $interests) && in_array($code, ['CHEM', 'BIO'])) $score += 15;
//        if (in_array('ECONOMICS', $interests) && in_array($code, ['ALG', 'SOC'])) $score += 15;
//        if (in_array('HUMANITIES', $interests) && in_array($code, ['HIST', 'LIT', 'LANG'])) $score += 15;
//
//        return $score;
//    }
//
//    private static function generateTopSets(array $calculatedSubjects, array $interests): array
//    {
//        // Сортируем предметы по убыванию баллов
//        uasort($calculatedSubjects, function($a, $b) {
//            return $b['score'] <=> $a['score'];
//        });
//
//        $sortedCodes = array_keys($calculatedSubjects);
//        $recommendations = [];
//
//        if (count($sortedCodes) >= 3) {
//            // Набор №1: Комбинация из трех абсолютных лидеров скоринга
//            $recommendations[] = [
//                'set' => [
//                    $calculatedSubjects[$sortedCodes[0]]['title'],
//                    $calculatedSubjects[$sortedCodes[1]]['title'],
//                    $calculatedSubjects[$sortedCodes[2]]['title']
//                ],
//                'score' => round(($calculatedSubjects[$sortedCodes[0]]['score'] + $calculatedSubjects[$sortedCodes[1]]['score'] + $calculatedSubjects[$sortedCodes[2]]['score']) / 3),
//                'track' => 'Рекомендуемый основной профиль обучения'
//            ];
//
//            // Набор №2: Альтернативный набор (1, 2 и 4-й сильные предметы)
//            if (isset($sortedCodes[3])) {
//                $recommendations[] = [
//                    'set' => [
//                        $calculatedSubjects[$sortedCodes[0]]['title'],
//                        $calculatedSubjects[$sortedCodes[1]]['title'],
//                        $calculatedSubjects[$sortedCodes[3]]['title']
//                    ],
//                    'score' => round(($calculatedSubjects[$sortedCodes[0]]['score'] + $calculatedSubjects[$sortedCodes[1]]['score'] + $calculatedSubjects[$sortedCodes[3]]['score']) / 3),
//                    'track' => 'Дополнительный альтернативный профиль'
//                ];
//            }
//        } else {
//            $recommendations[] = [
//                'set' => ['Углубленная Математика', 'Информатика', 'Физика'],
//                'score' => 80,
//                'track' => 'Технический профиль (Базовая рекомендация)'
//            ];
//        }
//
//        return $recommendations;
//    }
//
//    private static function getSubjectTitleByCode(string $code): string
//    {
//        $titles = [
//            'RUS' => 'Русский язык', 'ALG' => 'Алгебра', 'GEO' => 'Геометрия',
//            'HIST' => 'История', 'SOC' => 'Обществознание', 'LIT' => 'Литература',
//            'GEOGR' => 'География', 'BIO' => 'Биология', 'PHYS' => 'Физика',
//            'CHEM' => 'Химия', 'LANG' => 'Иностранный язык', 'INFO' => 'Информатика'
//        ];
//        return $titles[$code] ?? $code;
//    }
//}
public static function calculate(Student_session $session): array
{
    $engine = new self();
    $sessionId = $session->id;
    $subjectScores = [];

    $subjects = DB::table('subject_catalogs')->get();

    Log::info('ScoringEngine: Найдено предметов в каталоге: ' . $subjects->count());

    foreach ($subjects as $subject) {
        $G = $engine->calcGradeIndex($sessionId, $subject->id);
        $A = $engine->calcAttitudeIndex($sessionId, $subject->code);
        $C = $engine->calcCognitiveFit($subject->code, $sessionId);
        $R = $engine->calcGoalRelevance($subject->code, $sessionId);
        $T = $engine->calcTimeFeasibility($session);
        $P = 50; // Пробы

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

    return $engine->buildRecommendations($session, $subjectScores);
}

private function buildCandidateSets($session): array
{
    $mandatory = ['RUS'];

    if ($session->exam_type === 'OGE') {
        // На ОГЭ в аттестат идут Алгебра и Геометрия раздельно
        if (!in_array('ALG', $mandatory)) $mandatory[] = 'ALG';
        if (!in_array('GEO', $mandatory)) $mandatory[] = 'GEO';
    } else {
        // На ЕГЭ они превращаются в один объединенный профильный экзамен
        if (!in_array('MATH_PROF', $mandatory)) $mandatory[] = 'MATH_PROF';
    }

    // Вытаскиваем предметы по выбору под текущий тип экзамена
    $electives = DB::table('subject_catalogs')
        ->join('exam_subject_links', 'subject_catalogs.id', '=', 'exam_subject_links.subject_id')
        ->where('exam_subject_links.exam_type', $session->exam_type)
        ->where('subject_catalogs.mandatory_flag', false)
        ->pluck('subject_catalogs.code')
        ->toArray();

    // Исключаем обязательные предметы, чтобы они не дублировались в парах по выбору
    // На ЕГЭ мы убираем из перебора ALG и GEO, так как они уже превратились в MATH_PROF
    $excludeList = array_merge($mandatory, ['ALG', 'GEO']);
    $electives = array_values(array_diff($electives, $excludeList));

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
