<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->string('profile_picture_path')->nullable()->after('profileable_id');
            $table->string('twitter_handle')->nullable()->after('bio');
            $table->string('linkedin_profile')->nullable()->after('twitter_handle');
            $table->text('bio')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn('profile_picture_path');
            $table->dropColumn('twitter_handle');
            $table->dropColumn('linkedin_profile');
            $table->string('bio')->nullable()->change();
        });
    }
};
