<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Make programme_id nullable on modules (modules can exist without a single programme)
        Schema::table('modules', function (Blueprint $table) {
            if (Schema::hasColumn('modules', 'programme_id')) {
                $table->foreignId('programme_id')->nullable()->change();
            }
        });

        // Pivot table for many-to-many programme <-> module relationship
        if (!Schema::hasTable('programme_module')) {
            Schema::create('programme_module', function (Blueprint $table) {
                $table->foreignId('programme_id')->constrained()->cascadeOnDelete();
                $table->foreignId('module_id')->constrained()->cascadeOnDelete();
                $table->primary(['programme_id', 'module_id']);
                $table->unsignedSmallInteger('order_column')->default(0);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('programme_module');
        Schema::table('modules', function (Blueprint $table) {
            if (Schema::hasColumn('modules', 'programme_id')) {
                $table->foreignId('programme_id')->change();
            }
        });
    }
};
