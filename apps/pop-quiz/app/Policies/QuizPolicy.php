<?php

namespace App\Policies;

use App\Models\Player;
use App\Models\Quiz;

final readonly class QuizPolicy
{
    /**
     * The quizzes of the house stay.
     */
    public function delete(Player $player, Quiz $quiz): bool
    {
        return $quiz->author()->is($player);
    }
}
