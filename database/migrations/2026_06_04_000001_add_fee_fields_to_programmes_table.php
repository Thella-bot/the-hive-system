<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('programmes', function (Blueprint $table) {
            if (!Schema::hasColumn('programmes', 'tools_cost')) {
                $table->decimal('tools_cost', 10, 2)->default(0)->after('uniform_fee');
            }
            if (!Schema::hasColumn('programmes', 'duration_months')) {
                $table->integer('duration_months')->nullable()->after('duration');
            }
            if (!Schema::hasColumn('programmes', 'requirements')) {
                $table->text('requirements')->nullable()->after('description');
            }
            if (!Schema::hasColumn('programmes', 'payment_method')) {
                $table->enum('payment_method', ['monthly', 'quarterly', 'both'])->default('both')->after('monthly_fee');
            }
            if (!Schema::hasColumn('programmes', 'intake_period')) {
                $table->string('intake_period')->nullable()->after('duration_months');
            }
            if (!Schema::hasColumn('programmes', 'career_opportunities')) {
                $table->text('career_opportunities')->nullable()->after('requirements');
            }
        });
    }

    public function down(): void
    {
        Schema::table('programmes', function (Blueprint $table) {
            $table->dropColumn([
                'tools_cost',
                'duration_months',
                'requirements',
                'payment_method',
                'intake_period',
                'career_opportunities',
            ]);
        });
    }
};
