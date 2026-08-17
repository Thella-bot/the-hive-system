<?php
declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('programme_id')->nullable()->constrained()->onDelete('set null');
        });

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

        Schema::table('modules', function (Blueprint $table) {
            if (Schema::hasColumn('modules', 'programme_id')) {
                $table->foreignId('programme_id')->nullable()->change();
            }
        });

        if (!Schema::hasTable('programme_module')) {
            Schema::create('programme_module', function (Blueprint $table) {
                $table->foreignId('programme_id')->constrained()->cascadeOnDelete();
                $table->foreignId('module_id')->constrained()->cascadeOnDelete();
                $table->primary(['programme_id', 'module_id']);
                $table->unsignedSmallInteger('order_column')->default(0);
            });
        }

        Schema::table('programme_module', function (Blueprint $table) {
            if (!Schema::hasColumn('programme_module', 'created_at')) {
                $table->timestamps();
            }
        });

        Schema::table('applications', function (Blueprint $table) {
            $table->json('attachments')->nullable()->after('notes');
        });

        Schema::table('programme_variants', function (Blueprint $table) {
            if (!Schema::hasColumn('programme_variants', 'registration_fee')) {
                $table->decimal('registration_fee', 10, 2)->default(0)->after('monthly_fee');
            }
            if (!Schema::hasColumn('programme_variants', 'academic_resource_fee')) {
                $table->decimal('academic_resource_fee', 10, 2)->default(0)->after('registration_fee');
            }
        });

        Schema::table('modules', function (Blueprint $table) {
            $table->string('type')->default('core');
        });

        Schema::table('short_courses', function (Blueprint $table) {
            $table->string('delivery_mode')->default('in_person')->after('type');
            $table->string('meeting_platform')->nullable()->after('delivery_mode');
            $table->string('meeting_link')->nullable()->after('meeting_platform');
            $table->string('location')->nullable()->after('meeting_link');
        });

        Schema::table('programmes', function (Blueprint $table) {
            $table->string('delivery_mode')->default('in_person')->after('duration');
            $table->string('meeting_platform')->nullable()->after('delivery_mode');
            $table->string('meeting_link')->nullable()->after('meeting_platform');
            $table->string('location')->nullable()->after('meeting_link');
        });

        Schema::table('modules', function (Blueprint $table) {
            $table->string('delivery_mode')->default('in_person')->after('type');
            $table->string('meeting_platform')->nullable()->after('delivery_mode');
            $table->string('meeting_link')->nullable()->after('meeting_platform');
            $table->string('location')->nullable()->after('meeting_link');
        });

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
        Schema::table('programme_module', function (Blueprint $table) {
            if (Schema::hasColumn('programme_module', 'year_level')) {
                $table->dropColumn('year_level');
            }
            if (Schema::hasColumn('programme_module', 'semester')) {
                $table->dropColumn('semester');
            }
        });

        Schema::table('modules', function (Blueprint $table) {
            $table->dropColumn(['delivery_mode', 'meeting_platform', 'meeting_link', 'location']);
        });

        Schema::table('programmes', function (Blueprint $table) {
            $table->dropColumn(['delivery_mode', 'meeting_platform', 'meeting_link', 'location']);
        });

        Schema::table('short_courses', function (Blueprint $table) {
            $table->dropColumn(['delivery_mode', 'meeting_platform', 'meeting_link', 'location']);
        });

        Schema::table('modules', function (Blueprint $table) {
            $table->dropColumn('type');
        });

        Schema::table('programme_variants', function (Blueprint $table) {
            $table->dropColumn(['registration_fee', 'academic_resource_fee']);
        });

        Schema::table('applications', function (Blueprint $table) {
            $table->dropColumn('attachments');
        });

        Schema::dropIfExists('programme_module');

        Schema::table('modules', function (Blueprint $table) {
            if (Schema::hasColumn('modules', 'programme_id')) {
                $table->foreignId('programme_id')->change();
            }
        });

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

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['programme_id']);
            $table->dropColumn('programme_id');
        });
    }
};