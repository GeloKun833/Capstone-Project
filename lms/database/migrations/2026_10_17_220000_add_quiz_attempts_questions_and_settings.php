<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('activities')) {
            Schema::table('activities', function (Blueprint $table) {
                if (! Schema::hasColumn('activities', 'available_at')) {
                    $table->dateTime('available_at')->nullable()->after('due_time');
                }
                if (! Schema::hasColumn('activities', 'time_limit_minutes')) {
                    $table->unsignedSmallInteger('time_limit_minutes')->nullable()->after('available_at');
                }
                if (! Schema::hasColumn('activities', 'attempts_allowed')) {
                    $table->unsignedTinyInteger('attempts_allowed')->nullable()->after('time_limit_minutes');
                }
                if (! Schema::hasColumn('activities', 'passing_score')) {
                    $table->unsignedTinyInteger('passing_score')->nullable()->after('attempts_allowed');
                }
                if (! Schema::hasColumn('activities', 'shuffle_questions')) {
                    $table->boolean('shuffle_questions')->default(false)->after('passing_score');
                }
                if (! Schema::hasColumn('activities', 'shuffle_choices')) {
                    $table->boolean('shuffle_choices')->default(false)->after('shuffle_questions');
                }
                if (! Schema::hasColumn('activities', 'show_correct_answers')) {
                    $table->boolean('show_correct_answers')->default(false)->after('shuffle_choices');
                }
            });
        }

        if (! Schema::hasTable('quiz_questions')) {
            Schema::create('quiz_questions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('activity_id')->constrained('activities')->cascadeOnDelete();
                $table->unsignedSmallInteger('position')->default(1);
                $table->string('question_type', 30);
                $table->text('prompt');
                $table->string('attachment_path')->nullable();
                $table->string('attachment_name')->nullable();
                $table->unsignedSmallInteger('points')->default(1);
                $table->text('explanation')->nullable();
                $table->json('choices')->nullable();
                $table->json('answer_key')->nullable();
                $table->timestamps();
                $table->index(['activity_id', 'position']);
            });
        }

        if (! Schema::hasTable('quiz_attempts')) {
            Schema::create('quiz_attempts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('activity_id')->constrained('activities')->cascadeOnDelete();
                $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
                $table->unsignedTinyInteger('attempt_number');
                $table->string('status', 20)->default('in_progress');
                $table->json('question_order')->nullable();
                $table->json('choice_orders')->nullable();
                $table->timestamp('started_at')->nullable();
                $table->timestamp('submitted_at')->nullable();
                $table->timestamp('expires_at')->nullable();
                $table->decimal('score', 8, 2)->nullable();
                $table->decimal('max_score', 8, 2)->nullable();
                $table->decimal('percentage', 5, 2)->nullable();
                $table->boolean('passed')->nullable();
                $table->string('letter_grade', 5)->nullable();
                $table->text('feedback')->nullable();
                $table->foreignId('graded_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('graded_at')->nullable();
                $table->timestamps();
                $table->unique(['activity_id', 'student_id', 'attempt_number'], 'quiz_attempts_student_unique');
                $table->index(['activity_id', 'student_id', 'status']);
            });
        }

        if (! Schema::hasTable('quiz_responses')) {
            Schema::create('quiz_responses', function (Blueprint $table) {
                $table->id();
                $table->foreignId('quiz_attempt_id')->constrained('quiz_attempts')->cascadeOnDelete();
                $table->foreignId('quiz_question_id')->constrained('quiz_questions')->cascadeOnDelete();
                $table->json('answer')->nullable();
                $table->decimal('points_earned', 8, 2)->nullable();
                $table->boolean('is_correct')->nullable();
                $table->text('feedback')->nullable();
                $table->timestamps();
                $table->unique(['quiz_attempt_id', 'quiz_question_id'], 'quiz_responses_unique');
            });
        }

        if (Schema::hasTable('quiz_questions') && Schema::hasColumn('activities', 'quiz_questions')) {
            $existing = DB::table('activities')
                ->where('activity_type', 'quiz')
                ->whereNotNull('quiz_questions')
                ->get(['id', 'quiz_questions']);

            foreach ($existing as $activity) {
                if (DB::table('quiz_questions')->where('activity_id', $activity->id)->exists()) {
                    continue;
                }
                $questions = json_decode($activity->quiz_questions, true);
                if (! is_array($questions)) {
                    continue;
                }
                foreach (array_values($questions) as $index => $question) {
                    if (! is_array($question) || trim((string) ($question['prompt'] ?? '')) === '') {
                        continue;
                    }
                    DB::table('quiz_questions')->insert([
                        'activity_id' => $activity->id,
                        'position' => $index + 1,
                        'question_type' => 'multiple_choice',
                        'prompt' => $question['prompt'],
                        'points' => max(1, (int) ($question['points'] ?? 1)),
                        'choices' => json_encode(array_values($question['choices'] ?? [])),
                        'answer_key' => json_encode([(int) ($question['answer'] ?? 0)]),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_responses');
        Schema::dropIfExists('quiz_attempts');
        Schema::dropIfExists('quiz_questions');

        if (! Schema::hasTable('activities')) {
            return;
        }

        Schema::table('activities', function (Blueprint $table) {
            foreach ([
                'show_correct_answers',
                'shuffle_choices',
                'shuffle_questions',
                'passing_score',
                'attempts_allowed',
                'time_limit_minutes',
                'available_at',
            ] as $column) {
                if (Schema::hasColumn('activities', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
