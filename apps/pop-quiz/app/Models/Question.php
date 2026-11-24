<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
    public $timestamps = false;

    protected $fillable = ['fun_fact', 'position', 'prompt'];

    /**
     * The riddles of Emoji Cinema are shown big.
     */
    public function isOnlyEmojis(): bool
    {
        return !preg_match('/[\pL\pN]/u', $this->prompt);
    }

    public function correctChoice(): Choice
    {
        return $this->choices->firstWhere('is_correct', true)
            ?? throw new \LogicException(\sprintf('The question %d has no right choice.', $this->id));
    }

    /**
     * @return BelongsTo<Quiz, $this>
     */
    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    /**
     * @return HasMany<Choice, $this>
     */
    public function choices(): HasMany
    {
        return $this->hasMany(Choice::class)->orderBy('position');
    }
}
