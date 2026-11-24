<?php

use App\Http\Controllers\AnswerController;
use App\Http\Controllers\AttemptController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PlayerController;
use App\Http\Controllers\QuizController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

// "login": where Laravel sends the guests
Route::middleware('guest')->group(function () {
    Route::get('/join', [PlayerController::class, 'create'])->name('login');
    Route::post('/join', [PlayerController::class, 'store'])->name('players.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/leave', [PlayerController::class, 'destroy'])->name('logout');

    Route::get('/quizzes/create', [QuizController::class, 'create'])->name('quizzes.create');
    Route::post('/quizzes', [QuizController::class, 'store'])->name('quizzes.store');
    Route::delete('/quizzes/{quiz}', [QuizController::class, 'destroy'])->name('quizzes.destroy')->can('delete', 'quiz');

    Route::post('/quizzes/{quiz}/attempts', [AttemptController::class, 'store'])->name('attempts.store');
    Route::get('/attempts/{attempt}', [AttemptController::class, 'show'])->name('attempts.show')->can('view', 'attempt');

    Route::post('/attempts/{attempt}/answers', [AnswerController::class, 'store'])->name('answers.store')->can('answer', 'attempt');
    Route::get('/attempts/{attempt}/answers/{answer}', [AnswerController::class, 'show'])->name('answers.show')->can('view', 'attempt')->scopeBindings();
});

Route::get('/quizzes/{quiz}', [QuizController::class, 'show'])->name('quizzes.show');
