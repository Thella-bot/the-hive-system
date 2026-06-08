<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('module_id')->constrained()->cascadeOnDelete();
            $table->string('academic_year', 10);
            $table->enum('semester', ['1', '2']);
            $table->timestamps();
            $table->unique(['user_id', 'module_id', 'academic_year', 'semester']);
        });

        Schema::create('module_user', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('module_id')->constrained()->onDelete('cascade');
            $table->primary(['user_id', 'module_id']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('module_user');
        Schema::dropIfExists('enrollments');
    }
};
