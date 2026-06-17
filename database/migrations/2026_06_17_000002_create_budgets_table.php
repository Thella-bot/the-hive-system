<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('budgets', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('academic_year');
            $table->integer('semester')->nullable();
            $table->foreignId('department_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('expense_category_id')->nullable()->constrained('expense_categories')->onDelete('set null');
            $table->decimal('approved_budget', 10, 2)->default(0);
            $table->decimal('allocated_amount', 10, 2)->default(0);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->enum('status', ['draft', 'active', 'closed'])->default('draft');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('academic_year');
            $table->index('semester');
            $table->index('department_id');
            $table->index('expense_category_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budgets');
    }
};