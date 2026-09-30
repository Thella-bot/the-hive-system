<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enrollment_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('module_id')->constrained()->cascadeOnDelete();

            // enrollment = student wants to join, deregistration = student wants to leave
            $table->enum('type', ['enrollment', 'deregistration'])->default('enrollment');

            $table->string('academic_year');
            $table->string('semester');
            $table->text('reason')->nullable();

            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_note')->nullable();

            $table->timestamps();

            $table->index(['status', 'type']);
            $table->index(['user_id', 'module_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enrollment_requests');
    }
};
