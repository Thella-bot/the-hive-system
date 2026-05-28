<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payslips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('pay_period_start');
            $table->date('pay_period_end');
            $table->decimal('gross_salary', 10, 2);
            $table->decimal('deductions', 10, 2)->default(0);
            $table->decimal('net_salary', 10, 2);
            $table->json('earnings')->nullable();
            $table->json('deductions_breakdown')->nullable();
            $table->decimal('leave_deducted', 10, 2)->default(0);
            $table->integer('leave_days_taken')->default(0);
            $table->json('leave_taken_detail')->nullable();
            $table->timestamps();
        });

        Schema::create('salary_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('base_salary', 10, 2);
            $table->decimal('housing_allowance', 10, 2)->default(0);
            $table->decimal('transport_allowance', 10, 2)->default(0);
            $table->decimal('medical_allowance', 10, 2)->default(0);
            $table->decimal('bonus_rate', 10, 2)->default(0);
            $table->decimal('tax_rate', 10, 2)->default(0);
            $table->decimal('pension_rate', 10, 2)->default(0);
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salary_profiles');
        Schema::dropIfExists('payslips');
    }
};