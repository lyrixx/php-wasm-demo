<?php

use App\Models\Attempt;
use App\Models\Choice;
use App\Models\Player;
use App\Models\Question;
use App\Models\Quiz;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quizzes', function (Blueprint $table) {
            $table->id();
            // Null for the quizzes of the house
            $table->foreignIdFor(Player::class, 'author_id')->nullable()->constrained('players')->cascadeOnDelete();
            $table->string('slug')->unique();
            $table->string('title');
            $table->string('emoji');
            $table->string('color');
            $table->string('description')->nullable();
            $table->timestamps();
        });

        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Quiz::class)->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('position');
            $table->string('prompt');
            $table->string('fun_fact')->nullable();
        });

        Schema::create('choices', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Question::class)->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('position');
            $table->string('label');
            $table->boolean('is_correct');
        });

        Schema::create('attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Quiz::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Player::class)->constrained()->cascadeOnDelete();
            $table->unsignedInteger('score')->default(0);
            $table->unsignedTinyInteger('correct_count')->default(0);
            $table->unsignedTinyInteger('streak')->default(0);
            $table->unsignedTinyInteger('best_streak')->default(0);
            // When the current question was first shown, in seconds: the
            // sooner the answer, the more points
            $table->foreignIdFor(Question::class, 'shown_question_id')->nullable()->constrained('questions')->nullOnDelete();
            $table->double('shown_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });

        Schema::create('answers', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Attempt::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Question::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Choice::class)->constrained()->cascadeOnDelete();
            $table->boolean('is_correct');
            $table->double('seconds');
            $table->unsignedTinyInteger('streak');
            $table->unsignedSmallInteger('speed_bonus');
            $table->unsignedSmallInteger('streak_bonus');
            $table->unsignedSmallInteger('points');
            $table->timestamps();

            // One answer per question, even after a double click
            $table->unique(['attempt_id', 'question_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('answers');
        Schema::dropIfExists('attempts');
        Schema::dropIfExists('choices');
        Schema::dropIfExists('questions');
        Schema::dropIfExists('quizzes');
    }
};
