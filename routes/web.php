<?php

use App\Http\Controllers\ExamFlow\AuthorityTestsPageController;
use App\Http\Controllers\ExamFlow\ExamSessionPageController;
use App\Http\Controllers\ExamFlow\WelcomePageController;
use Illuminate\Support\Facades\Route;

Route::get('/', WelcomePageController::class)->name('exam-flow.welcome');
Route::get('/egzaminy/{authority}', AuthorityTestsPageController::class)->name('exam-flow.authority-tests');
Route::get('/egzaminy/{authority}/{test}', ExamSessionPageController::class)->name('exam-flow.session');
Route::get('/egzaminy/{authority}/{test}/{class}', ExamSessionPageController::class)->name('exam-flow.session.with-class');
