<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payslips', function (Blueprint $table) {
            $table->decimal('leave_deducted', 10, 2)->default(0)->after('net_salary');
            $table->integer('leave_days_taken')->default(0)->after('leave_deducted');
            $table->json('leave_taken_detail')->nullable()->after('leave_days_taken');
        });
    }

    public function down(): void
    {
        Schema::table('payslips', function (Blueprint $table) {
            $table->dropColumn(['leave_deducted', 'leave_days_taken', 'leave_taken_detail']);
        });
    }
};