<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('placements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('programme_id')->constrained()->cascadeOnDelete();
            $table->string('organisation_name');
            $table->string('organisation_address');
            $table->string('supervisor_name');
            $table->string('supervisor_contact');
            $table->date('start_date');
            $table->date('end_date');
            $table->string('duration');
            $table->string('type');
            $table->string('status')->nullable();
            $table->text('learning_objectives')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('placements');
    }
};
