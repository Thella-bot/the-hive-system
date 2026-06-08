<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('programme_module', function (Blueprint $table) {
            if (!Schema::hasColumn('programme_module', 'created_at')) {
                $table->timestamps();
            }
        });
    }

    public function down(): void
    {
        Schema::table('programme_module', function (Blueprint $table) {
            if (Schema::hasColumn('programme_module', 'created_at')) {
                $table->dropColumn(['created_at', 'updated_at']);
            }
        });
    }
};
