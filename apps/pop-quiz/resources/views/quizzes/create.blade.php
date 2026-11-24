<x-layout title="Make a quiz" color="lime">
    <form class="panel builder" method="post" action="{{ route('quizzes.store') }}" data-builder data-min="{{ \App\Http\Requests\StoreQuizRequest::MIN_QUESTIONS }}" data-max="{{ \App\Http\Requests\StoreQuizRequest::MAX_QUESTIONS }}" data-once>
        @csrf
        <h1 class="shout">Make a quiz</h1>
        <p class="lead">{{ \App\Http\Requests\StoreQuizRequest::MIN_QUESTIONS }} to {{ \App\Http\Requests\StoreQuizRequest::MAX_QUESTIONS }} questions, four choices each: tick the right one, the game shuffles them.</p>

        @if ($errors->any())
            <div class="errors" role="alert">
                Almost there!
                <ul>
                    @foreach (array_slice($errors->all(), 0, 5) as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="builder-head">
            <label class="field emoji-field">
                <span>Emoji</span>
                <input type="text" name="emoji" value="{{ old('emoji', '🎉') }}" maxlength="{{ \App\Http\Requests\StoreQuizRequest::EMOJI_MAX_LENGTH }}" required>
            </label>
            <label class="field">
                <span>Title</span>
                <input type="text" name="title" value="{{ old('title') }}" maxlength="{{ \App\Http\Requests\StoreQuizRequest::TITLE_MAX_LENGTH }}" required>
            </label>
        </div>

        <label class="field">
            <span>Description, if you want one</span>
            <input type="text" name="description" value="{{ old('description') }}" maxlength="{{ \App\Http\Requests\StoreQuizRequest::DESCRIPTION_MAX_LENGTH }}">
        </label>

        <fieldset class="field">
            <legend>Color</legend>
            <div class="swatches">
                @foreach ($colors as $color)
                    <label class="pop-{{ $color->value }}">
                        <input type="radio" name="color" value="{{ $color->value }}" @checked(old('color', 'pink') === $color->value) required>
                        <span class="swatch"><span>{{ $color->label() }}</span></span>
                    </label>
                @endforeach
            </div>
        </fieldset>

        <div data-questions>
            @foreach ($questions as $index => $question)
                @include('quizzes.question-fields', ['index' => $index, 'number' => $loop->iteration, 'question' => $question])
            @endforeach
        </div>

        <template>
            @include('quizzes.question-fields', ['index' => '__index__', 'number' => '', 'question' => []])
        </template>

        <div class="builder-actions">
            <button class="button button-cyan" type="button" data-add hidden>+ Add a question</button>
            <button class="button button-big" type="submit">Publish!</button>
        </div>
    </form>
</x-layout>
