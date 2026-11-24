@props(['quiz'])
<a class="quiz-card pop-{{ $quiz->color->value }}" href="{{ route('quizzes.show', $quiz) }}">
    <span class="emoji" aria-hidden="true">{{ $quiz->emoji }}</span>
    <h3>{{ $quiz->title }}</h3>
    <p>{{ $quiz->description ?? ($quiz->author ? "A quiz by {$quiz->author->nickname}." : '') }}</p>
    <span class="meta">
        <span class="tag">{{ $quiz->questions_count }} questions</span>
        <span class="tag">{{ trans_choice('{0} No play yet|{1} 1 play|[2,*] :count plays', $quiz->plays_count) }}</span>
        @if ($quiz->best_score)
            <span class="tag">Best {{ number_format($quiz->best_score) }}</span>
        @endif
    </span>
</a>
