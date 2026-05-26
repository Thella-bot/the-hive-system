<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('programmes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('duration');
            $table->decimal('total_price', 10, 2)->default(0); 
            $table->decimal('monthly_fee', 10, 2)->default(0);
            $table->decimal('registration_fee', 10, 2)->default(0);
            $table->decimal('academic_resource_fee', 10, 2)->default(0);
            $table->decimal('uniform_fee', 10, 2)->default(0);
            $table->foreignId('department_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('programmes');
    }
};
