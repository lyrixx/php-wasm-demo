<?php

namespace App\Http\Controllers;

use App\Models\Attempt;
use App\Models\Quiz;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttemptController extends Controller
{
    public function store(Request $request, Quiz $quiz): RedirectResponse
    {
        $attempt = Attempt::start($quiz, $request->user());

        return redirect()->route('attempts.show', $attempt);
    }

    /**
     * The current question, or the score once they are all answered.
     */
    public function show(Attempt $attempt): View
    {
        $attempt->load('quiz.questions.choices');

        if ($attempt->isFinished()) {
            return view('attempts.result', [
                'answers' => $attempt->answers()->with('question.choices', 'choice')->get(),
                'attempt' => $attempt,
                'leaderboard' => Attempt::leaderboard($attempt->quiz, 5),
            ]);
        }

        $question = $attempt->currentQuestion()
            ?? throw new \LogicException(\sprintf('The attempt %d has no question left, and is not finished.', $attempt->id));
        $attempt->show($question, microtime(true));

        return view('attempts.question', [
            'attempt' => $attempt,
            'question' => $question,
            'secondsLeft' => max(0, Attempt::SPEED_BONUS_SECONDS - (microtime(true) - $attempt->shown_at)),
        ]);
    }
}
