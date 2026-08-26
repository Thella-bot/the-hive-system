<?php
declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('message_reactions')) {
            Schema::create('message_reactions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('message_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('emoji', 10);
                $table->timestamps();

                // One reaction per user per message per emoji
                $table->unique(['message_id', 'user_id', 'emoji']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('message_reactions');
    }
};
