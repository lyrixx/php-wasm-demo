@props(['title' => null, 'color' => 'yellow'])
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ? "{$title} · Pop Quiz!" : 'Pop Quiz!' }}</title>
    <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Ctext y='.9em' font-size='90'%3E💥%3C/text%3E%3C/svg%3E">
    <link rel="stylesheet" href="{{ asset('css/pop.css') }}">
    <script src="{{ asset('js/pop.js') }}" defer></script>
</head>
<body class="pop-{{ $color }}">
    <header class="masthead">
        <a class="logo" href="{{ route('home') }}"><span>Pop</span> <span>Quiz!</span></a>
        <nav aria-label="Player">
            @auth
                <a class="button button-small button-cyan" href="{{ route('quizzes.create') }}">+ Make a quiz</a>
                <span class="player-chip"><span class="avatar" aria-hidden="true">{{ auth()->user()->avatar }}</span>{{ auth()->user()->nickname }}</span>
                <form method="post" action="{{ route('logout') }}">
                    @csrf
                    <button class="link-button" type="submit">Switch player</button>
                </form>
            @elseif (!request()->routeIs('login'))
                <a class="button button-small button-cyan" href="{{ route('login') }}">Join the game</a>
            @endauth
        </nav>
    </header>

    @if (session('status'))
        <p class="toast" role="status">{{ session('status') }}</p>
    @endif

    <main>
        {{ $slot }}
    </main>

    <footer class="site-footer">
        <span>Laravel {{ app()->version() }} on PHP {{ PHP_VERSION }}, in your browser tab</span>
    </footer>
</body>
</html>
