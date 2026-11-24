<x-layout color="blue">
    <section class="hero">
        <div class="burst"><h1 class="shout">Pop<br>Quiz!</h1></div>
        <div>
            <div class="bubble">
                <p class="lead">Four quizzes of the house, the ones players make, and eight bots who want the top of the leaderboard. Answer fast: the clock gives points.</p>
            </div>
            @auth
                <a class="button button-big button-yellow" href="#quizzes">Pick a quiz ↓</a>
            @else
                <a class="button button-big button-yellow" href="{{ route('login') }}">Join the game!</a>
            @endauth
        </div>
    </section>

    <div class="home-grid">
        <div id="quizzes">
            <h2 class="section-title shout">The house mix</h2>
            <ul class="cards">
                @foreach ($house as $quiz)
                    <li><x-quiz-card :quiz="$quiz" /></li>
                @endforeach
            </ul>

            <h2 class="section-title shout">Made by players</h2>
            <ul class="cards">
                @foreach ($community as $quiz)
                    <li><x-quiz-card :quiz="$quiz" /></li>
                @endforeach
                <li>
                    <a class="quiz-card quiz-card-new" href="{{ route('quizzes.create') }}">
                        <span class="emoji" aria-hidden="true">✏️</span>
                        <h3>Make your own</h3>
                        <p>Three to ten questions, four choices each, and a fun fact if you have one.</p>
                    </a>
                </li>
            </ul>
        </div>

        <aside class="panel hall-of-fame" aria-labelledby="hall-of-fame">
            <h2 id="hall-of-fame">🏆 Hall of fame</h2>
            @if ($hallOfFame->isEmpty())
                <p class="board-empty">Nobody yet: be the first!</p>
            @else
                <ol class="board">
                    @foreach ($hallOfFame as $player)
                        <li @class(['you' => auth()->id() === $player->id])>
                            <span class="rank">{{ $loop->iteration }}</span>
                            <span aria-hidden="true">{{ $player->avatar }}</span>
                            <span class="who">{{ $player->nickname }}</span>
                            <span class="score">{{ number_format($player->total_score) }}</span>
                        </li>
                    @endforeach
                </ol>
            @endif
            <p class="hint">The best score of each player on each quiz, added up.</p>
        </aside>
    </div>
</x-layout>
