<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Achievements ───────────────────────────────────────────────────
        Schema::create('achievements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type', 50);
            $table->string('title');
            $table->string('awarded_by')->nullable();
            $table->date('awarded_at')->nullable();
            $table->string('photo_url')->nullable();
            $table->boolean('is_public')->default(true);
            $table->timestamps();
        });

        // ── Polls & Votes ──────────────────────────────────────────────────
        Schema::create('polls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('question');
            $table->enum('type', ['binary', 'choice'])->default('choice');
            $table->json('options')->nullable();
            $table->dateTime('expires_at')->nullable();
            $table->timestamps();
        });

        Schema::create('poll_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('poll_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('choice')->nullable();
            $table->timestamps();
            $table->unique(['poll_id', 'user_id']);
        });

        // ── Programme Waitlists ───────────────────────────────────────────
        Schema::create('programme_waitlists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('programme_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('joined_at');
            $table->unsignedInteger('position');
            $table->timestamp('notified_at')->nullable();
            $table->timestamps();
            $table->unique(['programme_id', 'user_id']);
        });

        // ── Keys & Assignments ─────────────────────────────────────────────
        Schema::create('keys', function (Blueprint $table) {
            $table->id();
            $table->string('label');
            $table->string('location')->nullable();
            $table->enum('status', ['available', 'issued', 'lost'])->default('available');
            $table->timestamps();
        });

        Schema::create('key_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('key_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('issued_at')->nullable();
            $table->timestamp('returned_at')->nullable();
            $table->string('status', 20)->default('issued');
            $table->timestamps();
        });

        // ── Visitor Logs ───────────────────────────────────────────────────
        Schema::create('visitor_logs', function (Blueprint $table) {
            $table->id();
            $table->string('visitor_name');
            $table->string('id_number')->nullable();
            $table->foreignId('host_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('purpose')->nullable();
            $table->timestamp('arrived_at');
            $table->timestamp('departed_at')->nullable();
            $table->string('status', 20)->default('checked_in');
            $table->timestamps();
        });

        // ── Suppliers ─────────────────────────────────────────────────────
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category')->nullable();
            $table->string('contact_name')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->date('contract_expiry')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // ── Uniform Requests ───────────────────────────────────────────────
        Schema::create('uniform_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('item_type');
            $table->string('size')->nullable();
            $table->unsignedInteger('quantity')->default(1);
            $table->enum('status', ['pending', 'approved', 'issued', 'rejected'])->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });

        // ── Attendances ───────────────────────────────────────────────────
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('event_id')->nullable()->constrained()->nullOnDelete();
            $table->string('event_type')->default('event');
            $table->unsignedInteger('timetable_slot_id')->nullable();
            $table->timestamp('checked_in_at');
            $table->string('method', 20)->default('qr');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
        Schema::dropIfExists('uniform_requests');
        Schema::dropIfExists('suppliers');
        Schema::dropIfExists('visitor_logs');
        Schema::dropIfExists('key_assignments');
        Schema::dropIfExists('keys');
        Schema::dropIfExists('programme_waitlists');
        Schema::dropIfExists('poll_votes');
        Schema::dropIfExists('polls');
        Schema::dropIfExists('achievements');
    }
};
