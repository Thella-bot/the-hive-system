<?php
declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Change user_id to nullable with set null on delete
        // This preserves messages when users are deleted
        Schema::table('messages', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->foreignId('user_id')->nullable()->change();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });

        // Change module_id to nullable to support non-module channels
        Schema::table('messages', function (Blueprint $table) {
            $table->dropForeign(['module_id']);
            $table->foreignId('module_id')->nullable()->change();
            $table->foreign('module_id')->references('id')->on('modules')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->foreignId('user_id')->nullable(false)->change();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->dropForeign(['module_id']);
            $table->foreignId('module_id')->nullable(false)->change();
            $table->foreign('module_id')->references('id')->on('modules')->onDelete('cascade');
        });
    }
};
