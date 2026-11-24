<?php

namespace App\Models;

use App\Enums\Rating;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

/**
 * A player who plays a quiz, one question after the other.
 */
class Attempt extends Model
{
    public const int POINTS = 100;
    public const int SPEED_BONUS = 50;
    public const int SPEED_BONUS_SECONDS = 15;
    public const int STREAK_BONUS = 20;
    public const int STREAK_BONUS_MAX = 80;

    /**
     * The defaults of the columns, that a new attempt counts from before it
     * is read back.
     */
    protected $attributes = [
        'best_streak' => 0,
        'correct_count' => 0,
        'score' => 0,
        'streak' => 0,
    ];

    public static function start(Quiz $quiz, Player $player): self
    {
        $attempt = new self();
        $attempt->quiz()->associate($quiz);
        $attempt->player()->associate($player);
        $attempt->save();

        return $attempt;
    }

    /**
     * The best score of each player on the quiz.
     *
     * @return Collection<int, self>
     */
    public static function leaderboard(Quiz $quiz, int $limit): Collection
    {
        return self::query()
            ->whereBelongsTo($quiz)
            ->whereNotNull('finished_at')
            ->select('player_id')
            ->selectRaw('max(score) as best_score')
            ->groupBy('player_id')
            ->orderByDesc('best_score')
            ->orderBy('player_id')
            ->limit($limit)
            ->with('player')
            ->get();
    }

    public function isFinished(): bool
    {
        return null !== $this->finished_at;
    }

    public function currentQuestion(): ?Question
    {
        return $this->quiz->questions->get($this->answers()->count());
    }

    /**
     * The choices of a question, shuffled: the right one is not always the
     * first, and the order stays the same for the attempt, across reloads.
     *
     * @return Collection<int, Choice>
     */
    public function shuffledChoices(Question $question): Collection
    {
        return $question->choices
            ->sortBy(fn (Choice $choice): string => hash('xxh32', "{$this->id}-{$choice->id}"))
            ->values();
    }

    /**
     * The clock starts when the question is first shown: showing it again, on
     * a reload, does not give more time.
     */
    public function show(Question $question, float $now): void
    {
        if ($this->shownQuestion()->is($question)) {
            return;
        }

        $this->shownQuestion()->associate($question);
        $this->shown_at = $now;
        $this->save();
    }

    public function answer(Choice $choice, float $now): Answer
    {
        $question = $this->currentQuestion() ?? throw new \LogicException(\sprintf('The attempt %d is finished.', $this->id));
        if ($choice->question()->isNot($question)) {
            throw new \LogicException(\sprintf('The choice %d is not one of the question %d.', $choice->id, $question->id));
        }

        // A question answered without being shown, from another tab, is slow
        $seconds = $this->shownQuestion()->is($question) ? max(0.0, $now - $this->shown_at) : (float) self::SPEED_BONUS_SECONDS;
        $correct = $choice->is_correct;

        $this->streak = $correct ? $this->streak + 1 : 0;
        $this->best_streak = max($this->best_streak, $this->streak);
        $speedBonus = $correct ? (int) round(self::SPEED_BONUS * max(0.0, 1 - $seconds / self::SPEED_BONUS_SECONDS)) : 0;
        $streakBonus = $correct ? min(self::STREAK_BONUS_MAX, self::STREAK_BONUS * ($this->streak - 1)) : 0;
        $points = $correct ? self::POINTS + $speedBonus + $streakBonus : 0;

        $answer = new Answer([
            'is_correct' => $correct,
            'points' => $points,
            'seconds' => $seconds,
            'speed_bonus' => $speedBonus,
            'streak' => $this->streak,
            'streak_bonus' => $streakBonus,
        ]);
        $answer->attempt()->associate($this);
        $answer->question()->associate($question);
        $answer->choice()->associate($choice);
        $answer->save();

        $this->score += $points;
        $this->correct_count += (int) $correct;
        $this->shownQuestion()->dissociate();
        $this->shown_at = null;
        if (null === $this->currentQuestion()) {
            $this->finished_at = now();
        }
        $this->save();

        return $answer;
    }

    public function rating(): Rating
    {
        return Rating::for($this->correct_count, $this->quiz->questions->count());
    }

    /**
     * Where the score is among the best ones of the other players: ties share
     * a rank.
     */
    public function rank(): int
    {
        $betterPlayers = self::query()
            ->whereBelongsTo($this->quiz)
            ->whereNotNull('finished_at')
            ->where('player_id', '!=', $this->player_id)
            ->select('player_id')
            ->groupBy('player_id')
            ->havingRaw('max(score) > ?', [$this->score]);

        return DB::query()->fromSub($betterPlayers, 'better_players')->count() + 1;
    }

    public function isPersonalBest(): bool
    {
        return !self::query()
            ->whereBelongsTo($this->player)
            ->whereBelongsTo($this->quiz)
            ->whereNotNull('finished_at')
            ->whereKeyNot($this->getKey())
            ->where('score', '>=', $this->score)
            ->exists();
    }

    /**
     * @return BelongsTo<Quiz, $this>
     */
    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    /**
     * @return BelongsTo<Player, $this>
     */
    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    /**
     * @return BelongsTo<Question, $this>
     */
    public function shownQuestion(): BelongsTo
    {
        return $this->belongsTo(Question::class, 'shown_question_id');
    }

    /**
     * @return HasMany<Answer, $this>
     */
    public function answers(): HasMany
    {
        return $this->hasMany(Answer::class)->orderBy('id');
    }

    protected function casts(): array
    {
        return [
            'finished_at' => 'datetime',
            'shown_at' => 'float',
        ];
    }
}
