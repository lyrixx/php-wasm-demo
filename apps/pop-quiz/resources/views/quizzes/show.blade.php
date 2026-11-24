<x-layout :title="$quiz->title" :color="$quiz->color->value">
    <div class="quiz-intro">
        <section class="panel">
            <div class="quiz-head">
                <span class="emoji" aria-hidden="true">{{ $quiz->emoji }}</span>
                <div>
                    <h1 class="shout">{{ $quiz->title }}</h1>
                    <p>{{ $quiz->isOfTheHouse() ? 'A quiz of the house' : "A quiz by {$quiz->author->nickname}" }} · {{ $quiz->questions_count }} questions</p>
                </div>
            </div>

            @if ($quiz->description)
                <p class="lead">{{ $quiz->description }}</p>
            @endif

            <ul class="rules">
                <li><strong>+{{ \App\Models\Attempt::POINTS }}</strong> for each right answer</li>
                <li><strong>+{{ \App\Models\Attempt::SPEED_BONUS }}</strong> at most, the faster the better: the clock runs {{ \App\Models\Attempt::SPEED_BONUS_SECONDS }} seconds</li>
                <li><strong>+{{ \App\Models\Attempt::STREAK_BONUS }}</strong> for each right answer in a row, up to +{{ \App\Models\Attempt::STREAK_BONUS_MAX }}</li>
            </ul>

            <div class="actions">
                <form method="post" action="{{ route('attempts.store', $quiz) }}" data-once>
                    @csrf
                    <button class="button button-big button-yellow" type="submit">Play!</button>
                </form>
                @can('delete', $quiz)
                    <form method="post" action="{{ route('quizzes.destroy', $quiz) }}">
                        @csrf
                        @method('DELETE')
                        <button class="link-button" type="submit">Delete this quiz</button>
                    </form>
                @endcan
            </div>
        </section>

        <aside class="panel" aria-labelledby="leaderboard">
            <h2 class="side-title" id="leaderboard">Leaderboard</h2>
            <x-board :attempts="$leaderboard" />
        </aside>
    </div>
</x-layout>
