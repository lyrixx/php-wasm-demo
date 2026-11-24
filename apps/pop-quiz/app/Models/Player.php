<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\DB;

/**
 * Who plays: a nickname and an avatar, no password. The database is in the
 * browser of the player, the nicknames of this browser are theirs.
 */
class Player extends Authenticatable
{
    public const array AVATARS = ['🦄', '🐙', '🦖', '🐸', '🦊', '🐼', '👾', '🤖', '🍕', '🌈', '🔥', '🎸'];

    protected $fillable = ['avatar', 'is_bot', 'nickname'];

    /**
     * Comes back as who played with this nickname in this browser, or starts.
     * The bots keep theirs: the validation refuses them.
     */
    public static function join(string $nickname, string $avatar): self
    {
        $player = self::firstOrNew(['nickname' => $nickname]);
        $player->avatar = $avatar;
        $player->save();

        return $player;
    }

    /**
     * The players with the most points, counting their best score on each
     * quiz: playing a quiz again does not add up.
     *
     * @return Collection<int, self>
     */
    public static function hallOfFame(int $limit): Collection
    {
        $bestScores = Attempt::query()
            ->whereNotNull('finished_at')
            ->select('player_id')
            ->selectRaw('max(score) as best_score')
            ->groupBy('player_id', 'quiz_id');

        $totals = DB::query()
            ->fromSub($bestScores, 'best_scores')
            ->select('player_id')
            ->selectRaw('sum(best_score) as total_score')
            ->selectRaw('count(*) as quiz_count')
            ->groupBy('player_id');

        return self::query()
            ->joinSub($totals, 'totals', 'totals.player_id', '=', 'players.id')
            ->select('players.*', 'totals.total_score', 'totals.quiz_count')
            ->orderByDesc('total_score')
            ->limit($limit)
            ->get();
    }

    /**
     * @return HasMany<Attempt, $this>
     */
    public function attempts(): HasMany
    {
        return $this->hasMany(Attempt::class);
    }

    /**
     * @return HasMany<Quiz, $this>
     */
    public function quizzes(): HasMany
    {
        return $this->hasMany(Quiz::class, 'author_id');
    }

    protected function casts(): array
    {
        return [
            'is_bot' => 'boolean',
        ];
    }
}
