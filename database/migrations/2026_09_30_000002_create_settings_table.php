<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            // Cast hints for the settings form: string, boolean, integer, decimal, json
            $table->string('type')->default('string');
            $table->string('group')->default('general');
            $table->string('label');
            $table->string('description')->nullable();
            // Public settings are safe to expose to any authenticated user.
            $table->boolean('is_public')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
