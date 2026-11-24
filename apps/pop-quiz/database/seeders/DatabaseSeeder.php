<?php

namespace Database\Seeders;

use App\Models\Attempt;
use App\Models\Player;
use App\Models\Quiz;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Random\Engine\Mt19937;
use Random\Randomizer;

/**
 * Runs in the browser, on the first visit: the quizzes of the house, and bots
 * that play them, for the leaderboards. A seed gives everyone the same bots,
 * and the same scores.
 */
class DatabaseSeeder extends Seeder
{
    /**
     * Nickname, avatar, and how often they are right.
     */
    private const array BOTS = [
        ['BubbleBoss', '🐙', 0.9],
        ['CaptainConfetti', '🎸', 0.55],
        ['DiscoDolphin', '🌈', 0.8],
        ['FunkyFalafel', '🍕', 0.4],
        ['LaserLlama', '🦄', 0.7],
        ['NeonNinja', '👾', 0.85],
        ['PixelPanda', '🐼', 0.65],
        ['TurboTaco', '🔥', 0.5],
    ];

    public function run(): void
    {
        // One transaction: a thousand queries, written at once
        DB::transaction(function (): void {
            $randomizer = new Randomizer(new Mt19937(1994));

            $quizzes = array_map(Quiz::ofTheHouse(...), HouseQuizzes::all());

            foreach (self::BOTS as [$nickname, $avatar, $skill]) {
                $bot = Player::create(['avatar' => $avatar, 'is_bot' => true, 'nickname' => $nickname]);

                foreach ($randomizer->pickArrayKeys($quizzes, $randomizer->getInt(2, \count($quizzes))) as $key) {
                    $this->play($quizzes[$key], $bot, $skill, $randomizer);
                }
            }
        });
    }

    private function play(Quiz $quiz, Player $bot, float $skill, Randomizer $randomizer): void
    {
        $attempt = Attempt::start($quiz, $bot);
        $clock = (float) $randomizer->getInt(1_000_000, 2_000_000);

        while (null !== $question = $attempt->currentQuestion()) {
            $attempt->show($question, $clock);
            $right = $randomizer->nextFloat() < $skill;
            $choices = $question->choices->where('is_correct', $right)->values();
            $clock += $randomizer->getFloat(1.5, 14.0);

            $attempt->answer($choices[$randomizer->getInt(0, $choices->count() - 1)], $clock);
        }
    }
}
