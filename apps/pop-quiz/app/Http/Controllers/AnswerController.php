<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAnswerRequest;
use App\Models\Answer;
use App\Models\Attempt;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AnswerController extends Controller
{
    public function store(StoreAnswerRequest $request, Attempt $attempt): RedirectResponse
    {
        $now = microtime(true);
        $answer = DB::transaction(static fn (): Answer => $attempt->answer($request->choice(), $now));

        return redirect()->route('answers.show', [$attempt, $answer]);
    }

    public function show(Attempt $attempt, Answer $answer): View
    {
        $answer->load('question.choices', 'choice');

        return view('answers.show', [
            'answer' => $answer,
            'attempt' => $attempt->load('quiz.questions'),
        ]);
    }
}
