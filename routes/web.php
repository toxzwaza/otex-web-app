<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QuestionnaireController;
use App\Http\Controllers\TestController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::get('/questionnaire/{uid}', [QuestionnaireController::class , 'index'])->name('questionnaire.index');

Route::post('/questionnaire/store', [QuestionnaireController::class, 'store'])->name('questionnaire.store');

Route::get('/test', [TestController::class , 'index'])->name('test');

// 管理画面ルート
Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
Route::get('/admin/export', [AdminController::class, 'export'])->name('admin.export');
Route::get('/admin/questionnaire/{id}', [AdminController::class, 'show'])->name('admin.questionnaire.show');
Route::put('/admin/questionnaire/{id}', [AdminController::class, 'updateQuestionnaire'])->name('admin.questionnaire.update');

Route::get('/admin/clear', [AdminController::class, 'clear'])->name('admin.clear');