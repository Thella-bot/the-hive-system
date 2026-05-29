<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Leave Requests ──────────────────────────────────────────────────
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->string('type')->default('annual')->change();
            $table->boolean('half_day')->default(false)->after('type');
            $table->text('rejection_reason')->nullable()->after('reason');
            $table->boolean('is_cancelled')->default(false)->after('rejection_reason');
            $table->timestamp('cancelled_at')->nullable()->after('is_cancelled');
        });

        // ── Chat Channels ───────────────────────────────────────────────────
        Schema::create('chat_channels', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('channel_type', ['module', 'department', 'general', 'direct'])->default('module');
            $table->foreignId('channel_id')->nullable();
            $table->json('participants')->nullable();
            $table->timestamps();
            $table->unique(['channel_type', 'channel_id']);
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->foreignId('chat_channel_id')->nullable()->after('module_id')->constrained('chat_channels')->nullOnDelete();
        });

        // ── Announcements ──────────────────────────────────────────────────
        Schema::table('announcements', function (Blueprint $table) {
            $table->text('body_html')->nullable()->after('body');
            $table->json('attachments')->nullable()->after('body_html');
            $table->enum('priority', ['normal', 'urgent', 'emergency'])->default('normal')->after('is_pinned');
        });

        Schema::create('announcement_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('announcement_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('file_path');
            $table->unsignedInteger('size');
            $table->foreignId('uploaded_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });

        // ── Events ────────────────────────────────────────────────────────
        Schema::table('events', function (Blueprint $table) {
            $table->string('location')->nullable()->after('description');
        });

        Schema::create('event_rsvps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['attending', 'maybe', 'declined'])->default('attending');
            $table->timestamps();
            $table->unique(['event_id', 'user_id']);
        });

        // ── Document Acknowledgements ──────────────────────────────────────
        Schema::create('document_acknowledgements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('acknowledged_at');
            $table->timestamps();
            $table->unique(['document_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_acknowledgements');
        Schema::dropIfExists('event_rsvps');
        Schema::table('events', fn (Blueprint $t) => $t->dropColumn('location'));
        Schema::dropIfExists('announcement_attachments');
        Schema::table('announcements', fn (Blueprint $t) => $t->dropColumn(['body_html', 'attachments', 'priority']));
        Schema::table('messages', fn (Blueprint $t) => $t->dropForeign(['chat_channel_id'])->dropColumn('chat_channel_id'));
        Schema::dropIfExists('chat_channels');
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->dropColumn(['half_day', 'rejection_reason', 'is_cancelled', 'cancelled_at']);
            $table->enum('type', ['annual', 'sick', 'other'])->default('annual')->change();
        });
    }
};
