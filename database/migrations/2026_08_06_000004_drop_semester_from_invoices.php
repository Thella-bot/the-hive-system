<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('invoices', 'semester')) {
            return;
        }

        try {
            DB::statement('DROP INDEX invoices_semester_index');
        } catch (\Throwable $e) {
        }

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('semester');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->integer('semester')->nullable()->after('academic_year');
        });
    }
};