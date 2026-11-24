<x-layout title="Join" color="cyan">
    <form class="panel join" method="post" action="{{ route('players.store') }}">
        @csrf
        <h1 class="shout">Who's playing?</h1>
        <p class="lead">Pick a nickname and a face. No password: the database is in your browser, the nicknames of this browser are yours.</p>

        <label class="field">
            <span>Nickname</span>
            <input type="text" name="nickname" value="{{ old('nickname') }}" maxlength="{{ \App\Http\Requests\JoinRequest::NICKNAME_MAX_LENGTH }}" required autofocus autocomplete="nickname">
            @error('nickname')<p class="error">{{ $message }}</p>@enderror
        </label>

        <fieldset class="field">
            <legend>Avatar</legend>
            <div class="avatars">
                @foreach ($avatars as $avatar)
                    <label>
                        <input type="radio" name="avatar" value="{{ $avatar }}" @checked(old('avatar', $avatars[0]) === $avatar) required>
                        <span class="avatar-tile">{{ $avatar }}</span>
                    </label>
                @endforeach
            </div>
            @error('avatar')<p class="error">{{ $message }}</p>@enderror
        </fieldset>

        <button class="button button-big" type="submit">Let's play!</button>
    </form>
</x-layout>
