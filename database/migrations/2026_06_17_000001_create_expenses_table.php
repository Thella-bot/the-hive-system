<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expense_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('code')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->string('expense_number')->unique();
            $table->foreignId('expense_category_id')->nullable()->constrained('expense_categories')->onDelete('set null');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('vendor_id')->nullable()->constrained('suppliers')->onDelete('set null');
            $table->foreignId('budget_id')->nullable()->constrained('budgets')->onDelete('set null');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->decimal('amount', 10, 2);
            $table->text('description')->nullable();
            $table->date('expense_date')->nullable();
            $table->string('payment_method', 50)->nullable();
            $table->string('reference_number')->nullable();
            $table->date('approved_at')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'paid', 'cancelled'])->default('pending');
            $table->string('receipt_path')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('expense_category_id');
            $table->index('user_id');
            $table->index('budget_id');
            $table->index('expense_number');
            $table->index('status');
            $table->index('expense_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('expense_categories');
    }
};