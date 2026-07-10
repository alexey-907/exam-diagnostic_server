<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{
    public function index()
    {
        // 1. Сбор сессий учеников (ваш существующий код)
        $studentsData = DB::table('student_sessions')
            ->leftJoin('users', 'student_sessions.user_id', '=', 'users.id')
            ->select('student_sessions.*', 'users.name as student_name', 'users.email as student_email')
            ->orderBy('student_sessions.created_at', 'desc')
            ->get();

        foreach ($studentsData as $session) {
            $session->cognitive = DB::table('cognitive_results')
                ->where('session_id', $session->id)
                ->pluck('raw_score', 'test_code')
                ->toArray();

            $session->grades_list = DB::table('subject_grades')
                ->join('subject_catalogs', 'subject_grades.subject_id', '=', 'subject_catalogs.id')
                ->where('subject_grades.session_id', $session->id)
                ->select('subject_catalogs.title', 'subject_grades.quarter_grade', 'subject_grades.annual_grade')
                ->get();

            $session->attitudes_list = DB::table('subject_attitudes')
                ->where('session_id', $session->id)
                ->select('subject_code', DB::raw('ROUND(AVG(score), 1) as avg_score'))
                ->groupBy('subject_code')
                ->get();

            $session->clusters = DB::table('goal_profiles')
                ->where('session_id', $session->id)
                ->where('goal_type', 'PROFESSIONAL_CLUSTER')
                ->value('target_profession');
        }

        // 2. ДОБАВЛЕНО: Выгружаем ВСЕ вопросы из базы данных для просмотра и редактирования
        $allQuestions = DB::table('test_items')
            ->orderBy('test_code')
            ->orderBy('item_code')
            ->get();

        $tasksCount = DB::table('test_items')
            ->select('test_code', DB::raw('count(*) as count'))
            ->groupBy('test_code')
            ->pluck('count', 'test_code')
            ->toArray();

        return view('admin', compact('studentsData', 'tasksCount', 'allQuestions'));
    }

    // ... ваш существующий метод storeQuestion ...

    /**
     * ДОБАВЛЕНО: Обновление (изменение) вопроса в базе данных
     */
    public function updateQuestion(Request $request, $id)
    {
        $request->validate([
            'test_code' => 'required|in:WM,LR,AR,VR,SP,ATT,VM',
            'item_code' => 'required|string|max:40',
            'item_text' => 'required|string',
            'correct_answer' => 'required|string',
            'difficulty' => 'required|integer|between:1,5'
        ]);

        try {
            $optionsJson = null;
            $correctAnswerJson = null;

            // Обрабатываем структуру ответов в зависимости от кодов тестов
            if ($request->test_code === 'WM') {
                $optionsJson = json_encode(['display_data' => $request->input('display_data', '')]);
                $correctAnswerJson = json_encode([trim($request->correct_answer)]);
            }
            elseif (in_array($request->test_code, ['LR', 'VR', 'ATT', 'VM'])) {
                // Если options_text передан, пакуем его
                if ($request->has('options_text') && !empty($request->options_text)) {
                    $optionsArray = [];
                    $parts = explode(';', $request->options_text);
                    foreach ($parts as $p) {
                        $subParts = explode(':', $p, 2);
                        if (count($subParts) === 2) {
                            $optionsArray[] = ['key' => trim($subParts[0]), 'text' => trim($subParts[1])];
                        }
                    }
                    $optionsJson = json_encode($optionsArray);
                } else {
                    // Если поле пустое, сохраняем сырой json из скрытого поля (чтобы не затереть сложные структуры)
                    $optionsJson = $request->input('old_options_json', null);
                }
                $correctAnswerJson = json_encode([trim($request->correct_answer)]);
            }
            elseif ($request->test_code === 'SP') {
                $synonyms = array_map('trim', explode(';', $request->correct_answer));
                $correctAnswerJson = json_encode($synonyms);
            }
            else {
                $correctAnswerJson = json_encode([trim($request->correct_answer)]);
            }

            DB::table('test_items')->where('id', $id)->update([
                'test_code' => $request->test_code,
                'item_code' => trim($request->item_code),
                'item_text' => trim($request->item_text),
                'options_json' => $optionsJson,
                'correct_answer_json' => $correctAnswerJson,
                'difficulty' => $request->difficulty,
                'updated_at' => now()
            ]);

            return redirect()->back()->with('success', 'Вопрос успешно обновлен!');
        } catch (\Exception $e) {
            Log::error('Ошибка обновления вопроса: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Ошибка при обновлении вопроса.');
        }
    }

    /**
     * ДОБАВЛЕНО: Удаление вопроса из базы данных
     */
    public function deleteQuestion($id)
    {
        try {
            DB::table('test_items')->where('id', $id)->delete();
            return redirect()->back()->with('success', 'Вопрос полностью удален из базы тестов!');
        } catch (\Exception $e) {
            Log::error('Ошибка удаления вопроса: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Не удалось удалить вопрос из-за системной ошибки.');
        }
    }
}
