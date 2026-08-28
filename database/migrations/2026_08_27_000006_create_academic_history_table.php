<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('academic_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('academic_year_id')->constrained()->onDelete('cascade');
            $table->foreignId('programme_id')->constrained()->onDelete('cascade');
            $table->integer('year_level');
            $table->string('semester');
            $table->string('status');
            $table->decimal('gpa', 3, 2)->nullable();
            $table->integer('modules_enrolled')->default(0);
            $table->integer('modules_passed')->default(0);
            $table->integer('modules_failed')->default(0);
            $table->text('notes')->nullable();
            $table->timestamp('promoted_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'academic_year_id', 'semester']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('academic_histories');
    }
};
