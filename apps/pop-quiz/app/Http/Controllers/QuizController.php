<?php

namespace App\Http\Controllers;

use App\Enums\PopColor;
use App\Http\Requests\StoreQuizRequest;
use App\Models\Attempt;
use App\Models\Quiz;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class QuizController extends Controller
{
    public function show(Quiz $quiz): View
    {
        $quiz->load('author')->loadCount('questions');

        return view('quizzes.show', [
            'leaderboard' => Attempt::leaderboard($quiz, 10),
            'quiz' => $quiz,
        ]);
    }

    public function create(Request $request): View
    {
        // As many questions as were sent back, with their errors: what a
        // client sends may not be a list
        $questions = $request->old('questions');
        if (!\is_array($questions) || [] === $questions) {
            $questions = array_fill(0, StoreQuizRequest::MIN_QUESTIONS, []);
        }

        return view('quizzes.create', [
            'colors' => PopColor::cases(),
            'questions' => $questions,
        ]);
    }

    public function store(StoreQuizRequest $request): RedirectResponse
    {
        $quiz = DB::transaction(static fn (): Quiz => Quiz::by($request->user(), $request->draft()));

        return redirect()->route('quizzes.show', $quiz)->with('status', 'Your quiz is out. Play it first!');
    }

    public function destroy(Quiz $quiz): RedirectResponse
    {
        $quiz->delete();

        return redirect()->route('home')->with('status', "\"{$quiz->title}\" is gone.");
    }
}
