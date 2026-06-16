<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('programme_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('variant_id')->nullable()->constrained('programme_variants')->onDelete('set null');
            $table->string('academic_year')->nullable();
            $table->integer('semester')->nullable();
            $table->enum('type', ['registration', 'tuition', 'uniform', 'tools', 'resource', 'examination', 'other'])->default('registration');
            $table->decimal('amount', 10, 2);
            $table->text('description')->nullable();
            $table->date('due_date')->nullable();
            $table->date('issued_at')->nullable();
            $table->date('paid_at')->nullable();
            $table->enum('status', ['pending', 'partial', 'paid', 'overdue', 'cancelled'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('programme_id');
            $table->index('academic_year');
            $table->index('semester');
            $table->index('status');
            $table->index('invoice_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};