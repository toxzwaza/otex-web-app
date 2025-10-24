<?php

namespace App\Http\Controllers;

use App\Models\Questionnaire;
use App\Models\School;
use App\Models\SchoolDepartment;
use Illuminate\Http\Request;
use Inertia\Inertia;

class QuestionnaireController extends Controller
{
    //
    public function index($uid){
        // 方法1: Eager Loading (推奨) - N+1問題を解決
        $schools = School::with(['departments' => function($query) {
            $query->select('name', 'school_id');
        }])->get();


        return Inertia::render('Questionnaire/Index', ['uid' => $uid, 'schools' => $schools]);
    }

    public function store(Request $request){
        try {
            // バリデーション
            $validated = $request->validate([
                'uid' => 'required|string',
                'school' => 'required|string',
                'department' => 'required|string',
                'grade' => 'required|string',
                'gender' => 'required|string',
            ]);

            // アンケートデータを保存
            $questionnaire = Questionnaire::create($validated);

            return response()->json([
                'status' => true,
                'message' => 'アンケートが正常に保存されました',
                'data' => $questionnaire
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'バリデーションエラー',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'サーバーエラーが発生しました',
                'error' => $e->getMessage()
            ], 500);
        }
    }

}
