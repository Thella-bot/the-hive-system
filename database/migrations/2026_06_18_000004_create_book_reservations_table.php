<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('book_reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('book_id')->constrained('library_books')->onDelete('cascade');
            $table->date('reserved_at');
            $table->date('expires_at');
            $table->date('fulfilled_at')->nullable();
            $table->enum('status', ['pending', 'fulfilled', 'cancelled', 'expired'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('book_reservations');
    }
};