<?php

namespace App\Http\Controllers;

use App\Models\Player;
use App\Models\Quiz;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $quizzes = Quiz::query()
            ->with('author')
            ->withCount([
                'attempts as plays_count' => static fn (Builder $query) => $query->whereNotNull('finished_at'),
                'questions',
            ])
            ->withMax(['attempts as best_score' => static fn (Builder $query) => $query->whereNotNull('finished_at')], 'score')
            ->orderBy('id')
            ->get();

        return view('home', [
            'community' => $quizzes->reject->isOfTheHouse()->reverse(),
            'hallOfFame' => Player::hallOfFame(5),
            'house' => $quizzes->filter->isOfTheHouse(),
        ]);
    }
}
