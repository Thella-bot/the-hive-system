<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_reference')->unique();
            $table->foreignId('invoice_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('recorded_by')->nullable()->constrained('users')->onDelete('set null');
            $table->decimal('amount', 10, 2);
            $table->enum('payment_method', ['cash', 'bank_transfer', 'mobile_money', 'card', 'other'])->default('cash');
            $table->date('payment_date')->nullable();
            $table->date('recorded_at')->nullable();
            $table->enum('status', ['pending', 'completed', 'failed', 'refunded'])->default('pending');
            $table->text('notes')->nullable();
            $table->string('proof_path')->nullable();
            $table->timestamps();

            $table->index('invoice_id');
            $table->index('user_id');
            $table->index('recorded_by');
            $table->index('payment_reference');
            $table->index('status');
            $table->index('payment_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};