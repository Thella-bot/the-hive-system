<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Leave Requests Enhancements ─────────────────────────────────────
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->string('type')->default('annual')->change();
            if (!Schema::hasColumn('leave_requests', 'half_day')) {
                $table->boolean('half_day')->default(false)->after('type');
            }
            if (!Schema::hasColumn('leave_requests', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable()->after('reason');
            }
            if (!Schema::hasColumn('leave_requests', 'is_cancelled')) {
                $table->boolean('is_cancelled')->default(false)->after('rejection_reason');
            }
            if (!Schema::hasColumn('leave_requests', 'cancelled_at')) {
                $table->timestamp('cancelled_at')->nullable()->after('is_cancelled');
            }
        });

        // ── Chat Channels ───────────────────────────────────────────────────
        if (!Schema::hasTable('chat_channels')) {
            Schema::create('chat_channels', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->enum('channel_type', ['module', 'department', 'general', 'direct'])->default('module');
                $table->foreignId('channel_id')->nullable();
                $table->json('participants')->nullable();
                $table->timestamps();
                $table->unique(['channel_type', 'channel_id']);
            });
        }

        // ── Messages Enhancement ────────────────────────────────────────────
        Schema::table('messages', function (Blueprint $table) {
            if (!Schema::hasColumn('messages', 'chat_channel_id')) {
                $table->foreignId('chat_channel_id')->nullable()->constrained('chat_channels')->nullOnDelete();
            }
        });

        // ── Announcements Enhancements ────────────────────────────────────────
        Schema::table('announcements', function (Blueprint $table) {
            if (!Schema::hasColumn('announcements', 'body_html')) {
                $table->text('body_html')->nullable()->after('body');
            }
            if (!Schema::hasColumn('announcements', 'attachments')) {
                $table->json('attachments')->nullable()->after('body_html');
            }
            if (!Schema::hasColumn('announcements', 'priority')) {
                $table->enum('priority', ['normal', 'urgent', 'emergency'])->default('normal')->after('is_pinned');
            }
        });

        // ── Announcement Attachments ─────────────────────────────────────────
        if (!Schema::hasTable('announcement_attachments')) {
            Schema::create('announcement_attachments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('announcement_id')->constrained()->cascadeOnDelete();
                $table->string('name');
                $table->string('file_path');
                $table->unsignedInteger('size');
                $table->foreignId('uploaded_by')->constrained('users')->cascadeOnDelete();
                $table->timestamps();
            });
        }

        // ── Events Enhancement ──────────────────────────────────────────────
        Schema::table('events', function (Blueprint $table) {
            if (!Schema::hasColumn('events', 'location')) {
                $table->string('location')->nullable()->after('description');
            }
        });

        // ── Event RSVPs ──────────────────────────────────────────────────────
        if (!Schema::hasTable('event_rsvps')) {
            Schema::create('event_rsvps', function (Blueprint $table) {
                $table->id();
                $table->foreignId('event_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->enum('status', ['attending', 'maybe', 'declined'])->default('attending');
                $table->timestamps();
                $table->unique(['event_id', 'user_id']);
            });
        }

        // ── Document Acknowledgements ───────────────────────────────────────
        if (!Schema::hasTable('document_acknowledgements')) {
            Schema::create('document_acknowledgements', function (Blueprint $table) {
                $table->id();
                $table->foreignId('document_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->timestamp('acknowledged_at');
                $table->timestamps();
                $table->unique(['document_id', 'user_id']);
            });
        }

        // ── Achievements ───────────────────────────────────────────────────
        if (!Schema::hasTable('achievements')) {
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
        }

        // ── Polls & Votes ────────────────────────────────────────────────────
        if (!Schema::hasTable('polls')) {
            Schema::create('polls', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('question');
                $table->enum('type', ['binary', 'choice'])->default('choice');
                $table->json('options')->nullable();
                $table->dateTime('expires_at')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('poll_votes')) {
            Schema::create('poll_votes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('poll_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('choice')->nullable();
                $table->timestamps();
                $table->unique(['poll_id', 'user_id']);
            });
        }

        // ── Programme Waitlists ────────────────────────────────────────────
        if (!Schema::hasTable('programme_waitlists')) {
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
        }

        // ── Keys & Assignments ───────────────────────────────────────────────
        if (!Schema::hasTable('keys')) {
            Schema::create('keys', function (Blueprint $table) {
                $table->id();
                $table->string('label');
                $table->string('location')->nullable();
                $table->enum('status', ['available', 'issued', 'lost'])->default('available');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('key_assignments')) {
            Schema::create('key_assignments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('key_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->timestamp('issued_at')->nullable();
                $table->timestamp('returned_at')->nullable();
                $table->string('status', 20)->default('issued');
                $table->timestamps();
            });
        }

        // ── Visitor Logs ─────────────────────────────────────────────────────
        if (!Schema::hasTable('visitor_logs')) {
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
        }

        // ── Suppliers ────────────────────────────────────────────────────────
        if (!Schema::hasTable('suppliers')) {
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
        }

        // ── Uniform Requests ─────────────────────────────────────────────────
        if (!Schema::hasTable('uniform_requests')) {
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
        }

        // ── Attendances ─────────────────────────────────────────────────────
        if (!Schema::hasTable('attendances')) {
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

        // ── Programme Fee Fields ─────────────────────────────────────────────
        Schema::table('programmes', function (Blueprint $table) {
            if (!Schema::hasColumn('programmes', 'tools_cost')) {
                $table->decimal('tools_cost', 10, 2)->default(0)->after('uniform_fee');
            }
            if (!Schema::hasColumn('programmes', 'duration_months')) {
                $table->integer('duration_months')->nullable()->after('duration');
            }
            if (!Schema::hasColumn('programmes', 'requirements')) {
                $table->text('requirements')->nullable()->after('description');
            }
            if (!Schema::hasColumn('programmes', 'payment_method')) {
                $table->enum('payment_method', ['monthly', 'quarterly', 'both'])->default('both')->after('monthly_fee');
            }
            if (!Schema::hasColumn('programmes', 'intake_period')) {
                $table->string('intake_period')->nullable()->after('duration_months');
            }
            if (!Schema::hasColumn('programmes', 'career_opportunities')) {
                $table->text('career_opportunities')->nullable()->after('requirements');
            }
        });
    }

    public function down(): void
    {
        Schema::table('programmes', function (Blueprint $table) {
            $table->dropColumn(['tools_cost', 'duration_months', 'requirements', 'payment_method', 'intake_period', 'career_opportunities']);
        });
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
        Schema::dropIfExists('document_acknowledgements');
        Schema::dropIfExists('event_rsvps');
        Schema::dropIfExists('announcement_attachments');
        Schema::table('announcements', fn (Blueprint $t) => $t->dropColumn(['body_html', 'attachments', 'priority']));
        Schema::table('messages', fn (Blueprint $t) => $t->dropForeign(['chat_channel_id'])->dropColumn('chat_channel_id'));
        Schema::dropIfExists('chat_channels');
        Schema::table('events', fn (Blueprint $t) => $t->dropColumn('location'));
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->dropColumn(['half_day', 'rejection_reason', 'is_cancelled', 'cancelled_at']);
            $table->enum('type', ['annual', 'sick', 'other'])->default('annual')->change();
        });
    }
};
