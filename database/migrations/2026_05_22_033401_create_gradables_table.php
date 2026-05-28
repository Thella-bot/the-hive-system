<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gradables', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // 'assignment', 'quiz', 'exam', etc.
            $table->foreignId('module_id')->constrained()->onDelete('cascade');
            $table->foreignId('instructor_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('title');
            $table->text('description')->nullable();
            $table->timestamp('due_date')->nullable();
            $table->unsignedInteger('duration_minutes')->nullable();
            $table->unsignedInteger('max_file_size')->nullable();
            $table->string('allowed_types')->nullable();
            $table->unsignedInteger('max_marks')->nullable();
            $table->decimal('weight', 5, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gradables');
    }
};
