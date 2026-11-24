@props(['attempts', 'empty' => 'Nobody yet: be the first!'])
@if ($attempts->isEmpty())
    <p class="board-empty">{{ $empty }}</p>
@else
    <ol class="board">
        @foreach ($attempts as $attempt)
            <li @class(['you' => auth()->id() === $attempt->player_id])>
                <span class="rank">{{ $loop->iteration }}</span>
                <span aria-hidden="true">{{ $attempt->player->avatar }}</span>
                <span class="who">{{ $attempt->player->nickname }}</span>
                <span class="score">{{ number_format($attempt->best_score) }}</span>
            </li>
        @endforeach
    </ol>
@endif
