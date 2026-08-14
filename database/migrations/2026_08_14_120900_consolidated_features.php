<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cohorts', function (Blueprint $table) {
            $table->date('start_date')->nullable()->after('max_students');
            $table->date('end_date')->nullable()->after('start_date');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->foreign('user_id')->references('id')->on('users')->restrictOnDelete();
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->foreign('user_id')->references('id')->on('users')->restrictOnDelete();
            $table->json('items')->nullable()->after('amount');
        });

        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->foreign('user_id')->references('id')->on('users')->restrictOnDelete();
        });

        Schema::table('attendance', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->foreign('user_id')->references('id')->on('users')->restrictOnDelete();
        });

        Schema::table('student_grades', function (Blueprint $table) {
            $table->dropForeign(['student_id']);
            $table->foreign('student_id')->references('id')->on('users')->restrictOnDelete();
        });

        if (Schema::hasColumn('invoices', 'semester')) {
            try {
                DB::statement('DROP INDEX invoices_semester_index');
            } catch (\Throwable $e) {
            }

            Schema::table('invoices', function (Blueprint $table) {
                $table->dropColumn('semester');
            });
        }

        Schema::table('applications', function (Blueprint $table) {
            if (!Schema::hasColumn('applications', 'attachments')) {
                $table->json('attachments')->nullable()->after('notes');
            }
        });

        Schema::table('departments', function (Blueprint $table) {
            $table->boolean('is_academic')->default(true)->after('is_active');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('last_login_at')->nullable()->after('email_verified_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('last_login_at');
        });

        Schema::table('departments', function (Blueprint $table) {
            $table->dropColumn('is_academic');
        });

        Schema::table('applications', function (Blueprint $table) {
            if (Schema::hasColumn('applications', 'attachments')) {
                $table->dropColumn('attachments');
            }
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->integer('semester')->nullable()->after('academic_year');
        });

        Schema::table('student_grades', function (Blueprint $table) {
            $table->dropForeign(['student_id']);
            $table->foreign('student_id')->references('id')->on('users')->cascadeOnDelete();
        });

        Schema::table('attendance', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });

        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->dropColumn('items');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('cohorts', function (Blueprint $table) {
            $table->dropColumn(['start_date', 'end_date']);
        });
    }
};
