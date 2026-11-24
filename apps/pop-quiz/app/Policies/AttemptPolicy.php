<?php

namespace App\Policies;

use App\Models\Attempt;
use App\Models\Player;

final readonly class AttemptPolicy
{
    public function view(Player $player, Attempt $attempt): bool
    {
        return $attempt->player()->is($player);
    }

    public function answer(Player $player, Attempt $attempt): bool
    {
        return $this->view($player, $attempt) && !$attempt->isFinished();
    }
}
