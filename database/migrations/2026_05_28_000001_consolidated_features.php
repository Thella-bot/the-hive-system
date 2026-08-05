<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Bookmarks ───────────────────────────────────────────────────────
        if (!Schema::hasTable('bookmarks')) {
            Schema::create('bookmarks', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('bookmarkable_type');
                $table->unsignedBigInteger('bookmarkable_id');
                $table->string('note')->nullable();
                $table->timestamps();
                $table->unique(['user_id', 'bookmarkable_type', 'bookmarkable_id']);
                $table->index(['bookmarkable_type', 'bookmarkable_id']);
            });
        }

        // ── Revision Notes ───────────────────────────────────────────────────
        if (!Schema::hasTable('revision_notes')) {
            Schema::create('revision_notes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('module_id')->constrained()->cascadeOnDelete();
                $table->string('title');
                $table->text('content')->nullable();
                $table->enum('type', ['general', 'formula', 'tip', 'warning'])->default('general');
                $table->integer('order')->default(0);
                $table->timestamps();
            });
        }

        // ── Student Tasks ────────────────────────────────────────────────────
        if (!Schema::hasTable('student_tasks')) {
            Schema::create('student_tasks', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('gradable_id')->nullable()->constrained()->nullOnDelete();
                $table->string('title');
                $table->text('description')->nullable();
                $table->date('due_date')->nullable();
                $table->enum('priority', ['low', 'medium', 'high'])->default('medium');
                $table->boolean('completed')->default(false);
                $table->timestamps();
            });
        }

        // ── Gradable Attachments ─────────────────────────────────────────────
        if (!Schema::hasTable('gradable_attachments')) {
            Schema::create('gradable_attachments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('gradable_id')->constrained()->cascadeOnDelete();
                $table->string('title');
                $table->string('file_path');
                $table->unsignedBigInteger('file_size')->default(0);
                $table->string('mime_type')->nullable();
                $table->foreignId('uploaded_by')->constrained('users')->cascadeOnDelete();
                $table->timestamps();
            });
        }

        // ── Gradable Submission Enhancements ─────────────────────────────────
        Schema::table('gradables', function (Blueprint $table) {
            if (!Schema::hasColumn('gradables', 'submission_type')) {
                $table->string('submission_type')->default('file_upload')->after('type');
            }
            if (!Schema::hasColumn('gradables', 'time_limit_minutes')) {
                $table->unsignedInteger('time_limit_minutes')->nullable()->after('duration_minutes');
            }
            if (!Schema::hasColumn('gradables', 'max_attempts')) {
                $table->unsignedInteger('max_attempts')->nullable()->after('time_limit_minutes');
            }
            if (!Schema::hasColumn('gradables', 'show_correct_answers')) {
                $table->boolean('show_correct_answers')->default(false)->after('max_attempts');
            }
            if (!Schema::hasColumn('gradables', 'shuffle_questions')) {
                $table->boolean('shuffle_questions')->default(false)->after('show_correct_answers');
            }
            if (!Schema::hasColumn('gradables', 'shuffle_options')) {
                $table->boolean('shuffle_options')->default(false)->after('shuffle_questions');
            }
        });

        // ── Questions table ─────────────────────────────────────────────────
        if (!Schema::hasTable('gradable_questions')) {
            Schema::create('gradable_questions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('gradable_id')->constrained()->cascadeOnDelete();
                $table->string('type');
                $table->text('question_text');
                $table->unsignedInteger('points')->default(1);
                $table->unsignedInteger('sort_order')->default(0);
                $table->boolean('is_required')->default(true);
                $table->text('explanation')->nullable();
                $table->timestamps();
            });
        }

        // ── Options table ───────────────────────────────────────────────────
        if (!Schema::hasTable('gradable_question_options')) {
            Schema::create('gradable_question_options', function (Blueprint $table) {
                $table->id();
                $table->foreignId('gradable_question_id')->constrained()->cascadeOnDelete();
                $table->text('option_text');
                $table->boolean('is_correct')->default(false);
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamps();
            });
        }

        // ── Student answers table ────────────────────────────────────────────
        if (!Schema::hasTable('gradable_answers')) {
            Schema::create('gradable_answers', function (Blueprint $table) {
                $table->id();
                $table->foreignId('gradable_id')->constrained()->cascadeOnDelete();
                $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('gradable_question_id')->nullable()->constrained()->setNullOnDelete();
                $table->foreignId('gradable_question_option_id')->nullable()->constrained()->setNullOnDelete();
                $table->text('answer_text')->nullable();
                $table->boolean('is_correct')->nullable();
                $table->unsignedInteger('points_awarded')->nullable();
                $table->timestamp('answered_at')->nullable();
                $table->timestamps();
                $table->unique(['gradable_id', 'student_id', 'gradable_question_id'], 'gradable_answers_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('gradable_answers');
        Schema::dropIfExists('gradable_question_options');
        Schema::dropIfExists('gradable_questions');
        Schema::dropIfExists('gradable_attachments');
        Schema::dropIfExists('student_tasks');
        Schema::dropIfExists('revision_notes');
        Schema::dropIfExists('bookmarks');
        Schema::table('gradables', function (Blueprint $table) {
            $table->dropColumn(['submission_type', 'time_limit_minutes', 'max_attempts', 'show_correct_answers', 'shuffle_questions', 'shuffle_options']);
        });
    }
};
