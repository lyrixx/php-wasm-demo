<?php

namespace App\Data;

final readonly class QuestionDraft
{
    /**
     * @param list<string> $choices
     * @param int          $correctChoice the index of the right one in $choices
     */
    public function __construct(
        public string $prompt,
        public array $choices,
        public int $correctChoice,
        public ?string $funFact = null,
    ) {
        if (!\array_key_exists($correctChoice, $choices)) {
            throw new \LogicException(\sprintf('The question "%s" has no choice %d.', $prompt, $correctChoice));
        }
    }
}
