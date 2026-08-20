<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->unsignedInteger('annual_leave_days')->nullable()->default(null)->change();
            $table->unsignedInteger('leave_balance')->nullable()->default(null)->change();
        });
    }

    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->unsignedInteger('annual_leave_days')->default(20)->nullable(false)->change();
            $table->unsignedInteger('leave_balance')->default(20)->nullable(false)->change();
        });
    }
};
