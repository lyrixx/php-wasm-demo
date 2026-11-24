<?php

namespace App\Data;

use App\Enums\PopColor;

final readonly class QuizDraft
{
    /**
     * @param list<QuestionDraft> $questions
     */
    public function __construct(
        public string $title,
        public string $emoji,
        public PopColor $color,
        public ?string $description,
        public array $questions,
    ) {
    }
}
