<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TestController extends Controller
{


    public function index()
    {
        $subjects = DB::table('subject_catalogs')->get(['code', 'title', 'id']);
        $questions = DB::table('questions')->get(['step', 'question', 'id']);
        $cognitiveTasks = collect();
        $limits = [
            'WM' => 3,
            'LR' => 8,
            'AR' => 7,
            'VR' => 5,
            'SP' => 6,
            'ATT' => 4,
        ];

        foreach ($limits as $testCode => $limit) {
            $tasks = DB::table('test_items')
                ->where('test_code', $testCode)
                ->where('active', true)
                ->inRandomOrder() // Order by rand
                ->limit($limit)
                ->get(['test_code', 'item_code', 'item_text', 'options_json', 'correct_answer_json']);

            $cognitiveTasks = $cognitiveTasks->merge($tasks);
        }

        // Исклчение для VM
        $vmTask = DB::table('test_items')
            ->where('test_code', 'VM')
            ->where('active', true)
            ->get(['test_code', 'item_code', 'item_text', 'options_json', 'correct_answer_json']);
        $cognitiveTasks = $cognitiveTasks->merge($vmTask);
        return view('main', compact('subjects', 'questions', 'cognitiveTasks'));
    }
}
