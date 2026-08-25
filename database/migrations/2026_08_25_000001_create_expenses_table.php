<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->string('expense_number')->unique();
            $table->foreignId('expense_category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vendor_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->foreignId('budget_id')->nullable()->constrained()->nullOnDelete();
            $table->text('description');
            $table->decimal('amount', 12, 2);
            $table->date('expense_date');
            $table->string('payment_method')->nullable();
            $table->string('reference_number')->nullable();
            $table->string('status')->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('approved_at')->nullable();
            $table->string('receipt_path')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('expense_category_id');
            $table->index('user_id');
            $table->index('budget_id');
            $table->index('status');
            $table->index('expense_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
