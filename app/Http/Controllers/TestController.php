<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\School;
use App\Models\SchoolDepartment;

class TestController extends Controller
{
    //
    public function index()
    {
        $filePath = public_path('school.csv');
        if (file_exists($filePath)) {
            $file = fopen($filePath, 'r');
            $previousSchool = null;
            $currentSchool = null;

            while (($line = fgetcsv($file)) !== false) {
                $school_name = $line[1];
                $school_furi = $line[2];
                $school_department_name = $line[3];

                // 前の行のschool_nameと比較して新しい場合はnew Schoolで作成
                if ($previousSchool === null || $previousSchool->name !== $school_name) {
                    $currentSchool = new School();
                    $currentSchool->name = $school_name;
                    $currentSchool->furigana = $school_furi;
                    $currentSchool->save();

                    echo "<p>" . "新しい学校を作成しました: {$school_name}" . "</p>";
                    $previousSchool = $currentSchool;
                }

                $schoolDepartment = new SchoolDepartment();
                $schoolDepartment->school_id = $previousSchool->id;
                $schoolDepartment->name = $school_department_name;
                $schoolDepartment->save();

                echo "<p>" . "学科を追加しました: {$school_department_name} -> {$school_name}" . "</p>";
            }
            fclose($file);

            echo "\nCSVファイルのインポートが完了しました。\n";
        } else {
            echo "ファイルが存在しません。";
        }
    }
}
