<x-layout :title="$attempt->quiz->title" :color="$attempt->quiz->color->value">
    <section class="panel">
        <div @class(['verdict', 'verdict-right' => $answer->is_correct, 'verdict-wrong' => !$answer->is_correct])>
            <div class="burst"><strong class="shout">{{ $answer->is_correct ? 'Pow!' : 'Oops!' }}</strong></div>
            <div>
                @if ($answer->is_correct)
                    <p class="points shout">+{{ $answer->points }} points</p>
                    <ul class="breakdown">
                        <li>+{{ \App\Models\Attempt::POINTS }} right answer</li>
                        <li>+{{ $answer->speed_bonus }} speed, in {{ number_format($answer->seconds, 1) }} s</li>
                        @if ($answer->streak_bonus)
                            <li>+{{ $answer->streak_bonus }} streak: {{ $answer->streak }} in a row 🔥</li>
                        @endif
                    </ul>
                @else
                    <p class="points shout">No points</p>
                    <ul class="breakdown">
                        <li>Answered in {{ number_format($answer->seconds, 1) }} s</li>
                        <li>The streak starts again</li>
                    </ul>
                @endif
            </div>
        </div>

        <p @class(['prompt', 'emoji-prompt' => $answer->question->isOnlyEmojis()])>{{ $answer->question->prompt }}</p>
        <ul class="reveal">
            @foreach ($attempt->shuffledChoices($answer->question) as $choice)
                <li @class(['is-right' => $choice->is_correct, 'is-yours' => $answer->choice->is($choice)])>
                    <span>{{ $choice->label }}</span>
                    @if ($choice->is_correct)
                        <span class="mark">✓ The right one</span>
                    @elseif ($answer->choice->is($choice))
                        <span class="mark">✗ Yours</span>
                    @endif
                </li>
            @endforeach
        </ul>

        @if ($answer->question->fun_fact)
            <div class="bubble fun-fact">
                <h2>Fun fact!</h2>
                <p>{{ $answer->question->fun_fact }}</p>
            </div>
        @endif

        <div class="next">
            <a class="button button-big button-yellow" href="{{ route('attempts.show', $attempt) }}" autofocus>
                {{ $attempt->isFinished() ? 'See my score →' : 'Next question →' }}
            </a>
        </div>
    </section>

    @if ($answer->is_correct)
        <div data-confetti="30"></div>
    @endif
</x-layout>
