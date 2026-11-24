<x-layout :title="$attempt->quiz->title" :color="$attempt->quiz->color->value">
    <section class="panel">
        <div class="play-bar">
            <span class="tag">{{ $attempt->quiz->emoji }} Question {{ $question->position + 1 }}/{{ $attempt->quiz->questions->count() }}</span>
            <span class="tag">{{ number_format($attempt->score) }} points</span>
            @if ($attempt->streak >= 2)
                <span class="tag streak">🔥 {{ $attempt->streak }} in a row</span>
            @endif
        </div>

        {{-- Only the look of the clock: the server counts the seconds --}}
        <div class="timer" aria-hidden="true">
            <span style="--seconds-left: {{ number_format($secondsLeft, 2, '.', '') }}s; --fraction: {{ number_format($secondsLeft / \App\Models\Attempt::SPEED_BONUS_SECONDS, 4, '.', '') }}"></span>
        </div>

        <div class="bubble">
            <p @class(['prompt', 'emoji-prompt' => $question->isOnlyEmojis()]) id="prompt">{{ $question->prompt }}</p>
        </div>

        @error('choice')
            <p class="error" role="alert">{{ $message }}</p>
        @enderror

        <form class="choices" method="post" action="{{ route('answers.store', $attempt) }}" aria-labelledby="prompt" data-choices data-once>
            @csrf
            @foreach ($attempt->shuffledChoices($question) as $choice)
                <button class="choice" type="submit" name="choice" value="{{ $choice->id }}">
                    <span class="letter" aria-hidden="true">{{ chr(65 + $loop->index) }}</span>
                    <span>{{ $choice->label }}</span>
                </button>
            @endforeach
        </form>

        <p class="keys">Keys <kbd>1</kbd> to <kbd>4</kbd>, or <kbd>A</kbd> to <kbd>D</kbd>, answer too.</p>
    </section>
</x-layout>
