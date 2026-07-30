<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Different-duration offerings of the same programme (e.g. Gastronomy
     * Cooking and Patisserie at 3 months vs 6 months) carry their own
     * registration and book/resource fees, not just a different total and
     * monthly instalment. Mirrors the fee fields already on `programmes`.
     */
    public function up(): void
    {
        Schema::table('programme_variants', function (Blueprint $table) {
            if (!Schema::hasColumn('programme_variants', 'registration_fee')) {
                $table->decimal('registration_fee', 10, 2)->default(0)->after('monthly_fee');
            }
            if (!Schema::hasColumn('programme_variants', 'academic_resource_fee')) {
                $table->decimal('academic_resource_fee', 10, 2)->default(0)->after('registration_fee');
            }
        });
    }

    public function down(): void
    {
        Schema::table('programme_variants', function (Blueprint $table) {
            $table->dropColumn(['registration_fee', 'academic_resource_fee']);
        });
    }
};
