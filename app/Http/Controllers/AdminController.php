<?php

namespace App\Http\Controllers;

use App\Models\Questionnaire;
use App\Models\School;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminController extends Controller
{
    public function dashboard()
    {
        // アンケートデータを取得（最新順）
        $questionnaires = Questionnaire::orderBy('created_at', 'desc')->get();
        
        // 統計データを計算
        $totalResponses = $questionnaires->count();
        $schoolStats = $questionnaires->groupBy('school')->map(function ($items) {
            return $items->count();
        });
        $gradeStats = $questionnaires->groupBy('grade')->map(function ($items) {
            return $items->count();
        });
        $genderStats = $questionnaires->groupBy('gender')->map(function ($items) {
            return $items->count();
        });
        $departmentStats = $questionnaires->groupBy('department')->map(function ($items) {
            return $items->count();
        });

        return Inertia::render('Admin/Dashboard', [
            'questionnaires' => $questionnaires,
            'stats' => [
                'total' => $totalResponses,
                'schools' => $schoolStats,
                'grades' => $gradeStats,
                'genders' => $genderStats,
                'departments' => $departmentStats,
            ]
        ]);
    }

    public function export()
    {
        $questionnaires = Questionnaire::orderBy('created_at', 'desc')->get();
        
        $csvData = [];
        $csvData[] = ['ID', 'UID', '学校', '学科', '学年', '性別', '回答日時'];
        
        foreach ($questionnaires as $questionnaire) {
            $csvData[] = [
                $questionnaire->id,
                $questionnaire->uid,
                $questionnaire->school,
                $questionnaire->department,
                $questionnaire->grade,
                $questionnaire->gender,
                $questionnaire->created_at->format('Y-m-d H:i:s')
            ];
        }

        $filename = 'questionnaire_data_' . date('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($csvData) {
            $file = fopen('php://output', 'w');
            // BOMを追加してExcelで文字化けを防ぐ
            fwrite($file, "\xEF\xBB\xBF");
            
            foreach ($csvData as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }


    public function clear(Request $request){

        $questionnaires = Questionnaire::all();

        foreach( $questionnaires as $questionnaire ){
            $questionnaire->delete();
        }

        return redirect()->route('admin.dashboard')->with('success', 'アンケートデータが正常に削除されました');
    }
}
