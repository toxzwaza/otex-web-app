<?php

namespace App\Http\Controllers;

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
        $uid = $request->uid;
        $school = $request->school;
        $grade = $request->grade;
        $gender = $request->gender;

        
    }
}
