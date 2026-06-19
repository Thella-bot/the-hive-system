<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('convectionary_incomes', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->string('source'); // canteen, events, fundraising, donations, other
            $table->decimal('amount', 12, 2);
            $table->date('income_date');
            $table->string('description')->nullable();
            $table->string('payment_method')->nullable(); // cash, bank_transfer, mobile_money, card
            $table->string('status')->default('received'); // pending, received, cancelled
            $table->foreignId('recorded_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('convectionary_incomes');
    }
};