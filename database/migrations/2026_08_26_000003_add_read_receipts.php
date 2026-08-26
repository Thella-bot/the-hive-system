<?php
declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('message_reads')) {
            Schema::create('message_reads', function (Blueprint $table) {
                $table->id();
                $table->foreignId('message_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->timestamp('read_at');
                $table->timestamps();

                $table->unique(['message_id', 'user_id']);
            });
        }

        // Add read_count column to messages for quick stats
        Schema::table('messages', function (Blueprint $table) {
            if (!Schema::hasColumn('messages', 'read_count')) {
                $table->unsignedInteger('read_count')->default(0)->after('attachments');
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('message_reads');
        Schema::table('messages', function (Blueprint $table) {
            $table->dropColumn('read_count');
        });
    }
};
