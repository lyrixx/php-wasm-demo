{{-- $question is what was sent back, it may be anything --}}
<section class="question-block" data-question>
    <div class="question-block-head">
        <h2>Question <span data-number>{{ $number }}</span></h2>
        <button class="link-button" type="button" data-remove hidden>Remove</button>
    </div>

    <label class="field">
        <span>The question</span>
        <input type="text" name="questions[{{ $index }}][prompt]" value="{{ data_get($question, 'prompt') }}" maxlength="{{ \App\Http\Requests\StoreQuizRequest::PROMPT_MAX_LENGTH }}" required>
        @error("questions.{$index}.prompt")<p class="error">{{ $message }}</p>@enderror
    </label>

    <fieldset class="field">
        <legend>Four choices: tick the right one</legend>
        <div class="choice-inputs">
            @foreach (['A', 'B', 'C', 'D'] as $choice => $letter)
                <div class="choice-input">
                    <input type="radio" name="questions[{{ $index }}][correct]" value="{{ $choice }}" aria-label="{{ $letter }} is the right one" @checked((string) data_get($question, 'correct', '0') === (string) $choice) required>
                    <input type="text" name="questions[{{ $index }}][choices][{{ $choice }}]" value="{{ data_get($question, "choices.{$choice}") }}" placeholder="Choice {{ $letter }}" aria-label="Choice {{ $letter }}" maxlength="{{ \App\Http\Requests\StoreQuizRequest::CHOICE_MAX_LENGTH }}" required>
                </div>
            @endforeach
        </div>
        @error("questions.{$index}.choices.*")<p class="error">{{ $message }}</p>@enderror
        @error("questions.{$index}.correct")<p class="error">{{ $message }}</p>@enderror
    </fieldset>

    <label class="field">
        <span>A fun fact, shown after the answer, if you have one</span>
        <input type="text" name="questions[{{ $index }}][fun_fact]" value="{{ data_get($question, 'fun_fact') }}" maxlength="{{ \App\Http\Requests\StoreQuizRequest::FUN_FACT_MAX_LENGTH }}">
        @error("questions.{$index}.fun_fact")<p class="error">{{ $message }}</p>@enderror
    </label>
</section>
