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
        // UIDでレコードを検索、なければ新規作成（アクセス記録）
        $questionnaire = Questionnaire::where('uid', $uid)->first();
        
        if (!$questionnaire) {
            // レコードが存在しない場合、新規作成（アクセスのみ記録）
            $questionnaire = Questionnaire::create([
                'uid' => $uid,
                'school' => null,
                'department' => null,
                'grade' => null,
                'gender' => null,
            ]);
            $already_flg = false;
        } else {
            // 既存レコードがある場合、回答済みかどうかをチェック
            $already_flg = ($questionnaire->school && $questionnaire->department && 
                           $questionnaire->grade && $questionnaire->gender) ? true : false;
        }
        
        // 方法1: Eager Loading (推奨) - N+1問題を解決
        $schools = School::with(['departments' => function($query) {
            $query->select('name', 'school_id');
        }])->orderBy('furigana', 'asc')->get();

        return Inertia::render('Questionnaire/Index', [
            'uid' => $uid, 
            'schools' => $schools,
            'already_flg' => $already_flg,
            'existing_data' => $already_flg ? $questionnaire : null // 回答済みの場合のみデータを返す
        ]);
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

            // UIDをキーにレコードを検索し、更新または新規作成
            $questionnaire = Questionnaire::where('uid', $validated['uid'])->first();
            
            if ($questionnaire) {
                // 既存レコードを更新
                $questionnaire->update([
                    'school' => $validated['school'],
                    'department' => $validated['department'],
                    'grade' => $validated['grade'],
                    'gender' => $validated['gender'],
                ]);
                $message = 'アンケートが正常に更新されました';
            } else {
                // 何らかの不具合でレコードが見つからない場合、新規作成
                $questionnaire = Questionnaire::create($validated);
                $message = 'アンケートが正常に保存されました';
            }

            return response()->json([
                'status' => true,
                'message' => $message,
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
