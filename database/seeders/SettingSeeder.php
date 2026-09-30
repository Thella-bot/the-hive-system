<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeds the `settings` table with the keys the application reads.
 *
 * Existing rows are left untouched, so this is safe to re-run after new
 * settings are introduced.
 */
class SettingSeeder extends Seeder
{
    /**
     * key => [value, type, group, label, description, is_public]
     *
     * @return array<string, array{0: string, 1: string, 2: string, 3: string, 4: string, 5: bool}>
     */
    public static function definitions(): array
    {
        return [
            'institution.name' => [
                'Honey Bee Culinary Institute', 'string', 'institution',
                'Institution name', 'Shown on generated documents and the public site.', true,
            ],
            'institution.abbreviation' => [
                'HBCI', 'string', 'institution',
                'Abbreviation', 'Short form used in document headers.', true,
            ],
            'institution.address' => [
                'Maseru, Lesotho', 'string', 'institution',
                'Address', 'Postal address printed on letters.', true,
            ],
            'institution.phone' => [
                '+266 2231 7317', 'string', 'institution',
                'Phone', 'Main switchboard number.', true,
            ],
            'institution.email' => [
                'info@hbci.ac.ls', 'string', 'institution',
                'Email', 'General enquiries address.', true,
            ],

            'academic.default_credits_per_module' => [
                '15', 'integer', 'academic',
                'Default module credits', 'Applied when a module is created without an explicit credit value.', false,
            ],
            'academic.registration_fee' => [
                '500.00', 'decimal', 'academic',
                'Registration fee', 'Default registration fee on new student invoices.', false,
            ],
            'academic.student_number_prefix' => [
                'S', 'string', 'academic',
                'Student number prefix', 'Prepended to generated student numbers.', false,
            ],
            'academic.transcript_signatory_title' => [
                'Registrar', 'string', 'academic',
                'Transcript signatory title', 'Title printed under the transcript signature line.', false,
            ],

            'finance.default_pay_day' => [
                '25th', 'string', 'finance',
                'Default pay day', 'Used when generating monthly payslips.', false,
            ],
            'finance.currency_symbol' => [
                'BWP', 'string', 'finance',
                'Currency', 'ISO currency code used across finance screens.', false,
            ],
            'finance.allow_partial_payments' => [
                '1', 'boolean', 'finance',
                'Allow partial payments', 'When off, an invoice must be settled in full.', false,
            ],
            'finance.default_salary' => [
                '15000.00', 'decimal', 'finance',
                'Default starting salary', 'Pre-filled on new staff salary profiles.', false,
            ],

            'hr.default_leave_days' => [
                '20', 'integer', 'hr',
                'Default annual leave days', 'Leave balance granted to new staff.', false,
            ],
            'hr.default_notice_period' => [
                '1 Month', 'string', 'hr',
                'Default notice period', 'Used on staff appointment letters.', false,
            ],
            'hr.default_probation' => [
                '3 Months', 'string', 'hr',
                'Default probation period', 'Used on staff appointment letters.', false,
            ],

            'notifications.email_enabled' => [
                '1', 'boolean', 'notifications',
                'Send email notifications', 'Master switch for outbound notification email.', false,
            ],
            'notifications.emergency_broadcast' => [
                '1', 'boolean', 'notifications',
                'Emergency broadcast enabled', 'Allows staff to send an urgent broadcast banner.', false,
            ],
            'notifications.digest_frequency' => [
                'daily', 'string', 'notifications',
                'Digest frequency', 'How often the pending-items digest is generated.', false,
            ],
        ];
    }

    public function run(): void
    {
        $now = now();

        foreach (self::definitions() as $key => [$value, $type, $group, $label, $description, $isPublic]) {
            DB::table('settings')->insertOrIgnore([
                'key' => $key,
                'value' => $value,
                'type' => $type,
                'group' => $group,
                'label' => $label,
                'description' => $description,
                'is_public' => $isPublic,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
