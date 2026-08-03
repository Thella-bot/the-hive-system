<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('programmes', function (Blueprint $table) {
            $table->string('delivery_mode')->default('in_person')->after('duration');
            $table->string('meeting_platform')->nullable()->after('delivery_mode');
            $table->string('meeting_link')->nullable()->after('meeting_platform');
            $table->string('location')->nullable()->after('meeting_link');
        });
    }

    public function down(): void
    {
        Schema::table('programmes', function (Blueprint $table) {
            $table->dropColumn(['delivery_mode', 'meeting_platform', 'meeting_link', 'location']);
        });
    }
};
