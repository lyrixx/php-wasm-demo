<?php

namespace App\Http\Requests;

use App\Models\Attempt;
use App\Models\Choice;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAnswerRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            // A choice of the current question: an old tab, or a double
            // click, sends one of a question already answered
            'choice' => [
                'required',
                'integer',
                Rule::exists('choices', 'id')->where('question_id', $this->attempt()->currentQuestion()?->id),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'choice.exists' => 'This question has already been answered.',
        ];
    }

    public function choice(): Choice
    {
        return Choice::findOrFail($this->validated('choice'));
    }

    private function attempt(): Attempt
    {
        return $this->route('attempt');
    }
}
