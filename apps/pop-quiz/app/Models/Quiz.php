<?php

namespace App\Models;

use App\Data\QuizDraft;
use App\Enums\PopColor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Quiz extends Model
{
    protected $fillable = ['color', 'description', 'emoji', 'slug', 'title'];

    public static function ofTheHouse(QuizDraft $draft): self
    {
        return self::publish($draft, null);
    }

    public static function by(Player $author, QuizDraft $draft): self
    {
        return self::publish($draft, $author);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function isOfTheHouse(): bool
    {
        return null === $this->author_id;
    }

    /**
     * @return BelongsTo<Player, $this>
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'author_id');
    }

    /**
     * @return HasMany<Question, $this>
     */
    public function questions(): HasMany
    {
        return $this->hasMany(Question::class)->orderBy('position');
    }

    /**
     * @return HasMany<Attempt, $this>
     */
    public function attempts(): HasMany
    {
        return $this->hasMany(Attempt::class);
    }

    protected function casts(): array
    {
        return [
            'color' => PopColor::class,
        ];
    }

    private static function publish(QuizDraft $draft, ?Player $author): self
    {
        $quiz = new self([
            'color' => $draft->color,
            'description' => $draft->description,
            'emoji' => $draft->emoji,
            'slug' => self::uniqueSlug($draft->title),
            'title' => $draft->title,
        ]);
        $quiz->author()->associate($author);
        $quiz->save();

        foreach ($draft->questions as $position => $questionDraft) {
            $question = $quiz->questions()->create([
                'fun_fact' => $questionDraft->funFact,
                'position' => $position,
                'prompt' => $questionDraft->prompt,
            ]);

            foreach ($questionDraft->choices as $choicePosition => $label) {
                $question->choices()->create([
                    'is_correct' => $choicePosition === $questionDraft->correctChoice,
                    'label' => $label,
                    'position' => $choicePosition,
                ]);
            }
        }

        return $quiz;
    }

    private static function uniqueSlug(string $title): string
    {
        // A title of emojis only has no slug
        $base = Str::slug($title) ?: 'quiz';
        $slug = $base;
        for ($i = 2; self::where('slug', $slug)->exists(); ++$i) {
            $slug = "{$base}-{$i}";
        }

        return $slug;
    }
}
