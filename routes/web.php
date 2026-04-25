<?php

use App\Http\Controllers\ExamFlow\AuthorityTestsPageController;
use App\Http\Controllers\ExamFlow\ExamSessionPageController;
use App\Http\Controllers\ExamFlow\ModeSelectionPageController;
use App\Http\Controllers\ExamFlow\WelcomePageController;
use Illuminate\Support\Facades\Route;

Route::get('/', WelcomePageController::class)->name('exam-flow.welcome');

Route::prefix('/egzaminy')->group(function () {
    Route::get('/{authority}', AuthorityTestsPageController::class)->name('exam-flow.authority-tests');

    Route::get('/{authority}/{test}/{class}/tryb/{mode}', ExamSessionPageController::class)->name('exam-flow.session.mode.with-class');
    Route::get('/{authority}/{test}/tryb/{mode}', ExamSessionPageController::class)->name('exam-flow.session.mode');

    Route::get('/{authority}/{test}/{class}', ModeSelectionPageController::class)->name('exam-flow.mode-selection.with-class');
    Route::get('/{authority}/{test}', ModeSelectionPageController::class)->name('exam-flow.mode-selection');
})->whereAlphaNumeric('authority')->whereAlphaNumeric('test')->whereAlphaNumeric('class');
