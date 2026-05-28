<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('programme_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('programme_id')->constrained()->cascadeOnDelete();
            $table->string('label');
            $table->string('duration');
            $table->decimal('total_price', 10, 2)->default(0);
            $table->decimal('monthly_fee', 10, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->foreignId('programme_id')->constrained()->onDelete('cascade');
            $table->foreignId('variant_id')->nullable()->after('programme_id')->constrained('programme_variants')->nullOnDelete();
            $table->string('status')->default('pending');
            $table->text('notes')->nullable();

            // Admission fields - set when application is approved
            $table->timestamp('admitted_at')->nullable()->after('notes');

            // Registration fields - set when admitted user completes registration
            $table->string('registration_status')->default('pending')->after('admitted_at');
            $table->timestamp('registered_at')->nullable()->after('registration_status');
            $table->string('payment_proof_path')->nullable()->after('registered_at');
            $table->timestamp('payment_verified_at')->nullable()->after('payment_proof_path');
            $table->text('registration_notes')->nullable()->after('payment_verified_at');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applications');
        Schema::dropIfExists('programme_variants');
    }
};
