<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('disciplinary_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->string('warning_level')->nullable();
            $table->string('offence');
            $table->text('incident_description');
            $table->date('hearing_date');
            $table->date('effective_date');
            $table->string('duration')->nullable();
            $table->date('return_date')->nullable();
            $table->string('campus_access')->nullable();
            $table->date('surrender_date')->nullable();
            $table->date('review_date')->nullable();
            $table->json('grounds')->nullable();
            $table->string('policy_violated')->nullable();
            $table->json('corrective_actions')->nullable();
            $table->string('advisor_name')->nullable();
            $table->string('hr_rep')->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('status')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('disciplinary_actions');
    }
};
