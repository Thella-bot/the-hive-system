<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            $table->morphs('profileable');
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            $table->unsignedInteger('annual_leave_days')->default(20);
            $table->unsignedInteger('leave_balance')->default(20);
            // Staff fields
            $table->string('employee_number')->unique()->nullable();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->string('designation')->nullable();
            $table->string('specialization')->nullable();
            $table->text('bio')->nullable();
            $table->date('hire_date')->nullable();
            // Student fields
            $table->string('student_number')->unique()->nullable();
            $table->foreignId('cohort_id')->nullable()->constrained()->nullOnDelete();
            $table->date('enrollment_date')->nullable();
            $table->date('expected_graduation_date')->nullable();
            $table->date('graduation_date')->nullable();
            $table->string('status')->nullable();
            $table->json('dietary_restrictions')->nullable();
            $table->string('emergency_contact_relationship')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};