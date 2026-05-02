<?php

use App\Http\Controllers\Admin\AdminExamController;
use App\Http\Controllers\Admin\AdminQuestionController;
use App\Http\Controllers\Admin\Auth\AdminLoginPageController;
use App\Http\Controllers\Admin\Auth\AdminSessionController;
use App\Http\Controllers\ExamFlow\AuthorityTestsPageController;
use App\Http\Controllers\ExamFlow\ExamSessionPageController;
use App\Http\Controllers\ExamFlow\ModeSelectionPageController;
use App\Http\Controllers\ExamFlow\WelcomePageController;
use Illuminate\Support\Facades\Route;

Route::get('/', WelcomePageController::class)->name('exam-flow.welcome');
Route::redirect('/login', '/admin-panel/login')->name('login');

Route::prefix('/admin-panel')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('/login', AdminLoginPageController::class)->name('login');
        Route::post('/login', [AdminSessionController::class, 'store'])->name('session.store');
    });

    Route::middleware(['auth', 'admin'])->group(function () {
        Route::post('/logout', [AdminSessionController::class, 'destroy'])->name('session.destroy');
        Route::get('/', [AdminExamController::class, 'index'])->name('dashboard');
        Route::post('/tests', [AdminExamController::class, 'store'])->name('tests.store');
        Route::put('/tests/{examId}', [AdminExamController::class, 'update'])->name('tests.update');
        Route::delete('/tests/{examId}', [AdminExamController::class, 'destroy'])->name('tests.destroy');
        Route::get('/tests/{examId}/questions', [AdminQuestionController::class, 'index'])->name('tests.questions.index');
        Route::post('/tests/{examId}/questions', [AdminQuestionController::class, 'store'])->name('tests.questions.store');
        Route::put('/tests/{examId}/questions/{questionId}', [AdminQuestionController::class, 'update'])->name('tests.questions.update');
        Route::delete('/tests/{examId}/questions/{questionId}', [AdminQuestionController::class, 'destroy'])->name('tests.questions.destroy');
    });
});

Route::prefix('/egzaminy')->group(function () {
    Route::get('/{authority}', AuthorityTestsPageController::class)->name('exam-flow.authority-tests');

    Route::get('/{authority}/{test}/{class}/tryb/{mode}', ExamSessionPageController::class)->name('exam-flow.session.mode.with-class');
    Route::get('/{authority}/{test}/tryb/{mode}', ExamSessionPageController::class)->name('exam-flow.session.mode');

    Route::get('/{authority}/{test}/{class}', ModeSelectionPageController::class)->name('exam-flow.mode-selection.with-class');
    Route::get('/{authority}/{test}', ModeSelectionPageController::class)->name('exam-flow.mode-selection');
})->whereAlphaNumeric('authority')->whereAlphaNumeric('test')->whereAlphaNumeric('class');
