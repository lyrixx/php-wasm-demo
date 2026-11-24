<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Answer extends Model
{
    protected $fillable = ['is_correct', 'points', 'seconds', 'speed_bonus', 'streak', 'streak_bonus'];

    /**
     * @return BelongsTo<Attempt, $this>
     */
    public function attempt(): BelongsTo
    {
        return $this->belongsTo(Attempt::class);
    }

    /**
     * @return BelongsTo<Question, $this>
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    /**
     * @return BelongsTo<Choice, $this>
     */
    public function choice(): BelongsTo
    {
        return $this->belongsTo(Choice::class);
    }

    protected function casts(): array
    {
        return [
            'is_correct' => 'boolean',
            'seconds' => 'float',
        ];
    }
}
