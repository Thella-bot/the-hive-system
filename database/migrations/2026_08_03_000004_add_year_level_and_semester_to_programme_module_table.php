<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('programme_module')) {
            return;
        }

        Schema::table('programme_module', function (Blueprint $table) {
            if (!Schema::hasColumn('programme_module', 'year_level')) {
                $table->unsignedTinyInteger('year_level')->nullable();
            }

            if (!Schema::hasColumn('programme_module', 'semester')) {
                $table->unsignedTinyInteger('semester')->nullable();
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('programme_module')) {
            return;
        }

        Schema::table('programme_module', function (Blueprint $table) {
            if (Schema::hasColumn('programme_module', 'year_level')) {
                $table->dropColumn('year_level');
            }

            if (Schema::hasColumn('programme_module', 'semester')) {
                $table->dropColumn('semester');
            }
        });
    }
};
