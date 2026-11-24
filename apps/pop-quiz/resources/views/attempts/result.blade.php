@php($rating = $attempt->rating())
<x-layout title="Your score" :color="$attempt->quiz->color->value">
    <section class="result-head" @if (in_array($rating, [\App\Enums\Rating::PopStar, \App\Enums\Rating::RisingStar], true)) data-confetti="120" @endif>
        <div class="burst">
            <div>
                <div class="rating-emoji" aria-hidden="true">{{ $rating->emoji() }}</div>
                <h1 class="shout">{{ $rating->value }}!</h1>
                <div class="score">{{ number_format($attempt->score) }} points</div>
            </div>
        </div>
        @if ($attempt->isPersonalBest())
            <p class="sticker">New personal best!</p>
        @endif
    </section>

    <ul class="stats">
        <li><strong>{{ $attempt->correct_count }}/{{ $answers->count() }}</strong> right answers</li>
        <li><strong>{{ $attempt->best_streak }}</strong> in a row, at best</li>
        <li><strong>#{{ $attempt->rank() }}</strong> on the leaderboard</li>
    </ul>

    <div class="result-grid">
        <section class="panel" aria-labelledby="answers">
            <h2 class="side-title" id="answers">{{ $attempt->quiz->emoji }} {{ $attempt->quiz->title }}</h2>
            <ol class="recap">
                @foreach ($answers as $answer)
                    <li @class(['is-wrong' => !$answer->is_correct])>
                        <span class="mark" aria-label="{{ $answer->is_correct ? 'Right' : 'Wrong' }}">{{ $answer->is_correct ? '✓' : '✗' }}</span>
                        <span>
                            <span class="question">{{ $answer->question->prompt }}</span>
                            <span class="detail">
                                @if ($answer->is_correct)
                                    {{ $answer->choice->label }}, in {{ number_format($answer->seconds, 1) }} s
                                @else
                                    {{ $answer->choice->label }}? It was {{ $answer->question->correctChoice()->label }}.
                                @endif
                            </span>
                        </span>
                        <span class="points">+{{ $answer->points }}</span>
                    </li>
                @endforeach
            </ol>

            <div class="actions next">
                <a class="button button-paper" href="{{ route('home') }}">Other quizzes</a>
                <form method="post" action="{{ route('attempts.store', $attempt->quiz) }}" data-once>
                    @csrf
                    <button class="button button-yellow" type="submit">Play again!</button>
                </form>
            </div>
        </section>

        <aside class="panel" aria-labelledby="leaderboard">
            <h2 class="side-title" id="leaderboard">Leaderboard</h2>
            <x-board :attempts="$leaderboard" />
        </aside>
    </div>
</x-layout>
