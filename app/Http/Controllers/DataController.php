<?php

namespace App\Http\Controllers;

use App\Models\Student_session;
use App\Models\CognitiveResult;
use App\Models\GoalProfile;
use App\Services\ScoringEngine; // Импортируем наш математический движок
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;


class DataController extends Controller
{
    public function saveAll(Request $request)
    {
        $data = $request->all();
        if (empty($data)) {
            $data = json_decode($request->getContent(), true) ?? [];
        }

        Log::info('DataController принял пакет:', $data);

        // Валидация входящего JSON-пакета от TypeScript
        $validator = Validator::make($data, [
            'role' => 'required',
            'grade' => 'required|integer',
            'examType' => 'required',
            'region' => 'required',
            'targetTrack' => 'required',
            'subjects' => 'required|array',
            'cognitive' => 'required|array',
            'interests' => 'required|array'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation Error',
                'messages' => $validator->errors()->all()
            ], 422);
        }

        // Запускаем транзакцию базы данных для защиты от частичной или сломанной записи
        DB::beginTransaction();

        try {
            // 1. ШАГ 1: Создаем основную сессию студента
            $session = Student_session::create([
                'user_id' => auth()->id(),
                'role' => $data['role'],
                'grade' => $data['grade'],
                'exam_type' => $data['examType'],
                'region' => $data['region'],
                'target_track' => $data['targetTrack'],
                'target_year' => $data['targetYear'] ?? date('Y'),
                'status' => 'completed'
            ]);

            $sessionId = $session->id; // Получаем сгенерированный ID новой сессии

            // 2. ШАГ 2: Сохраняем оценки (в subject_grades) и анкеты отношения (в subject_attitudes)
            if (isset($data['subjects'])) {
                foreach ($data['subjects'] as $subject) {

                    $jsCode = $subject['subjectCode']; // Например: 'RUS', 'PHYS', 'ALG'

                    // Ищем ID предмета в вашем справочнике по его строковому коду
                    $subjectId = DB::table('subject_catalogs')
                        ->where('code', $jsCode)
                        ->value('id');

                    // Если предмет не найден в справочнике — логируем предупреждение и пропускаем итерацию
                    if (!$subjectId) {
                        Log::warn("Scoring Warning: Предмет с кодом {$jsCode} не обнаружен в таблице subject_catalogs!");
                        continue;
                    }

                    // А) Записываем чистые школьные оценки в таблицу subject_grades
                    DB::table('subject_grades')->insert([
                        'session_id'    => $sessionId,
                        'subject_id'    => $subjectId,
                        'quarter_grade' => $subject['quarterGrade'] ?? null,
                        'annual_grade'  => $subject['annualGrade'] ?? null,
                    ]);

                    // Б) Раскладываем ответы анкеты S4 по строкам в таблицу subject_attitudes
                    $detailedAnswers = $subject['attitude']['detailedAnswers'] ?? [];
                    foreach ($detailedAnswers as $questionId => $scoreValue) {

                        // Формируем строковый код вопроса, например: 'ATT_01', 'ATT_02'
                        $questionCode = 'ATT_0' . $questionId;

                        DB::table('subject_attitudes')->insert([
                            'session_id'    => $sessionId,
                            'subject_code'  => $jsCode,        // Строковый код предмета (RUS, PHYS)
                            'question_code' => $questionCode,  // Код вопроса (ATT_01)

                            // ИСПРАВЛЕНО: Передаем чистый integer (3 вместо 'ANS_3'),
                            // так как ваша колонка answer_code ожидает число!
                            'answer_code'   => intval($scoreValue),

                            'score'         => intval($scoreValue),  // Сама оценка 1-5
                            'created_at'    => now(),
                            'updated_at'    => now()
                        ]);
                    }
                }
            }

            // 3. ШАГ 3: Сохраняем когнитивные результаты (в таблицу cognitive_results)
            if (isset($data['cognitive'])) {
                $testKeys = [
                    'wmScore'  => 'WM',   // Рабочая память
                    'vmScore'  => 'VM',   // Вербальная память
                    'lrScore'  => 'LR',   // Логика
                    'arScore'  => 'AR',   // Абстракция
                    'vrScore'  => 'VR',   // Вербальное понимание
                    'spScore'  => 'SP',   // ИСПРАВЛЕНО: Добавлен пропущенный пространственный тест!
                    'attScore' => 'ATT'  // Саморегуляция
                ];

                foreach ($testKeys as $jsKey => $dbCode) {
                    if (isset($data['cognitive'][$jsKey])) {
                        $score = intval($data['cognitive'][$jsKey]);

                        CognitiveResult::create([
                            'session_id' => $sessionId,
                            'test_code' => $dbCode,
                            'raw_score' => $score,
                            'normalized_score' => $score,
                            'level_code' => $this->getLevelCode($score),
                            'interpretation' => $this->getInterpretation($dbCode, $score),
                        ]);
                    }
                }
            }

            // 4. ШАГ 4: Сохраняем профессиональные кластеры интересов (S12)
            if (isset($data['interests']) && !empty($data['interests'])) {

                // ИСПРАВЛЕНО: Объединяем выбранные кластеры в одну строку через запятую
                // для соблюдения правила УНИКАЛЬНОСТИ session_id в вашей таблице!
                $clustersString = implode(', ', $data['interests']);

                DB::table('goal_profiles')->insert([
                    'session_id'        => $sessionId,
                    'goal_type'         => 'PROFESSIONAL_CLUSTER',
                    'target_profession' => $clustersString, // Запишет строку вида: "IT, ECONOMICS"
                    'target_program'    => null,
                    'target_city'       => null,
                    'priority_type'     => 'PRIMARY',
                    'created_at'        => now(),
                    'updated_at'        => now()
                ]);
            }



            // Фиксируем транзакцию: все реляционные таблицы успешно и одновременно записаны!
            DB::commit();

            // 5. ЗАПУСКАЕМ SCORING ENGINE: Математический расчет топ-наборов по вашей реляционной БД
            $recommendations = ScoringEngine::calculate($session);

            Log::info('Scoring Engine результат:', $recommendations);

            // Отдаем успешный ответ и РЕАЛЬНЫЕ рекомендации обратно на фронтенд в TypeScript
            return response()->json([
                'success' => true,
                'session_id' => $sessionId,
                'message' => 'Данные сохранены, Scoring Engine успешно рассчитал профиль!',
                'recommendations' => $recommendations
            ]);

        } catch (\Exception $e) {
            // В случае любого сбоя полностью откатываем изменения, чтобы не замусорить БД
            DB::rollBack();
            Log::error('Ошибка сохранения данных в DataController: ' . $e->getMessage());
            return response()->json([
                'error' => 'Internal Server Error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    private function getLevelCode($score)
    {
        if ($score >= 70) return 'high';
        if ($score >= 40) return 'medium';
        return 'low';
    }

    private function getInterpretation($testCode, $score)
    {
        if ($score >= 70) return 'Сильная сторона';
        if ($score >= 40) return 'Средний уровень';
        return 'Зона риска';
    }

    public function exportPdf($sessionId)
    {
        // 1. Извлекаем сессию и привязанные когнитивные результаты
        $session = DB::table('student_sessions')
            ->leftJoin('users', 'student_sessions.user_id', '=', 'users.id')
            ->where('student_sessions.id', $sessionId)
            ->select('student_sessions.*', 'users.name as student_name', 'users.email as student_email')
            ->first();

        if (!$session) {
            abort(404, 'Diagnostic session not found.');
        }

        // 2. Собираем когнитивный профиль
        $cognitive = DB::table('cognitive_results')
            ->where('session_id', $sessionId)
            ->pluck('raw_score', 'test_code')
            ->toArray();

        // 3. Вызываем Scoring Engine для получения ТОП-1 набора и планов подготовки по часам
        // (Передаем объект сессии, предварительно обернув ее в модель, если нужно, или адаптировав метод)
        // Так как наш ScoringEngine ожидает модель Student_session, временно поднимем её:
        $sessionModel = \App\Models\Student_session::find($sessionId);
        $recommendations = ScoringEngine::calculate($sessionModel);

        // Берем самый первый (лучший) набор из массива
        $bestSet = $recommendations[0] ?? null;

        // 4. Передаем все массивы параметров в изолированную Blade-вьюшку отчета
        $pdf = Pdf::loadView('diagnostic_report', compact('session', 'cognitive', 'bestSet'));

        // Настраиваем параметры страницы (А4, книжная ориентация)
        $pdf->setPaper('a4', 'portrait');

        // Отдаем файл в браузер с уникальным именем сессии
        return $pdf->download('diagnostic_report_session_' . $sessionId . '.pdf');
    }
}

