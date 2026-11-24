<?php

namespace App\Http\Requests;

use App\Data\QuestionDraft;
use App\Data\QuizDraft;
use App\Enums\PopColor;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreQuizRequest extends FormRequest
{
    public const int CHOICES = 4;
    public const int MAX_QUESTIONS = 10;
    public const int MIN_QUESTIONS = 3;

    // The maxlength of the fields of the form, too
    public const int CHOICE_MAX_LENGTH = 60;
    public const int DESCRIPTION_MAX_LENGTH = 140;
    public const int EMOJI_MAX_LENGTH = 8;
    public const int FUN_FACT_MAX_LENGTH = 200;
    public const int PROMPT_MAX_LENGTH = 140;
    public const int TITLE_MAX_LENGTH = 50;

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'color' => ['required', Rule::enum(PopColor::class)],
            'description' => ['nullable', 'string', 'max:'.self::DESCRIPTION_MAX_LENGTH],
            'emoji' => ['required', 'string', 'max:'.self::EMOJI_MAX_LENGTH],
            'questions' => ['required', 'array', 'min:'.self::MIN_QUESTIONS, 'max:'.self::MAX_QUESTIONS],
            'questions.*.choices' => ['required', 'array', 'size:'.self::CHOICES],
            'questions.*.choices.*' => ['required', 'string', 'max:'.self::CHOICE_MAX_LENGTH],
            'questions.*.correct' => ['required', 'integer', 'between:0,'.(self::CHOICES - 1)],
            'questions.*.fun_fact' => ['nullable', 'string', 'max:'.self::FUN_FACT_MAX_LENGTH],
            'questions.*.prompt' => ['required', 'string', 'max:'.self::PROMPT_MAX_LENGTH],
            'title' => ['required', 'string', 'max:'.self::TITLE_MAX_LENGTH],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'questions.*.choices.*' => 'choice',
            'questions.*.correct' => 'right choice',
            'questions.*.fun_fact' => 'fun fact',
            'questions.*.prompt' => 'question',
        ];
    }

    public function draft(): QuizDraft
    {
        $data = $this->validated();

        return new QuizDraft(
            title: $data['title'],
            emoji: $data['emoji'],
            color: PopColor::from($data['color']),
            description: $data['description'] ?? null,
            questions: array_values(array_map(
                static fn (array $question): QuestionDraft => new QuestionDraft(
                    prompt: $question['prompt'],
                    choices: array_values($question['choices']),
                    correctChoice: (int) $question['correct'],
                    funFact: $question['fun_fact'] ?? null,
                ),
                $data['questions'],
            )),
        );
    }
}
