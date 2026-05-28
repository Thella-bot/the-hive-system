<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gradables', function (Blueprint $table) {
            // submission_type: 'file_upload' (default for backwards), 'online_fillable', 'online_multiple_choice'
            $table->string('submission_type')->default('file_upload')->after('type');
            // For online assessments, time limit in minutes (null = no limit)
            $table->unsignedInteger('time_limit_minutes')->nullable()->after('duration_minutes');
            // Number of attempts allowed (null = unlimited)
            $table->unsignedInteger('max_attempts')->nullable()->after('time_limit_minutes');
            // Show correct answers after submission
            $table->boolean('show_correct_answers')->default(false)->after('max_attempts');
            // Shuffle questions for each student
            $table->boolean('shuffle_questions')->default(false)->after('show_correct_answers');
            // Shuffle options for MCQ
            $table->boolean('shuffle_options')->default(false)->after('shuffle_questions');
        });

        // Questions table - stores questions for online assessments
        Schema::create('gradable_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gradable_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // 'multiple_choice', 'fill_in_blank', 'short_answer', 'essay'
            $table->text('question_text');
            $table->unsignedInteger('points')->default(1);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_required')->default(true);
            $table->text('explanation')->nullable(); // Shown after answering
            $table->timestamps();
        });

        // Options table - stores options for MCQ questions
        Schema::create('gradable_question_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gradable_question_id')->constrained()->cascadeOnDelete();
            $table->text('option_text');
            $table->boolean('is_correct')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // Student answers table - stores student responses
        Schema::create('gradable_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gradable_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('gradable_question_id')->nullable()->constrained()->setNullOnDelete();
            $table->foreignId('gradable_question_option_id')->nullable()->constrained()->setNullOnDelete();
            $table->text('answer_text')->nullable(); // For fill_in_blank, short_answer, essay
            $table->boolean('is_correct')->nullable(); // null for essay/manual grading
            $table->unsignedInteger('points_awarded')->nullable();
            $table->timestamp('answered_at')->nullable();
            $table->timestamps();

            $table->unique(['gradable_id', 'student_id', 'gradable_question_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gradable_answers');
        Schema::dropIfExists('gradable_question_options');
        Schema::dropIfExists('gradable_questions');
        Schema::table('gradables', function (Blueprint $table) {
            $table->dropColumn(['submission_type', 'time_limit_minutes', 'max_attempts', 'show_correct_answers', 'shuffle_questions', 'shuffle_options']);
        });
    }
};