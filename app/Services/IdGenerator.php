<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class IdGenerator
{
    /**
     * Minimum width of the trailing sequence segment (S20260417 -> "17").
     */
    public const SEQUENCE_MIN_WIDTH = 2;

    /**
     * Generate a unique student ID, e.g. S20260224
     *
     * @param  int|null  $year  Defaults to the current year. Pass the student's
     *                          enrolment year so IDs stay stable across calendar
     *                          years (and across programme transfers).
     */
    public static function generateStudentId(int $departmentId, ?int $year = null): string
    {
        return self::generate('student', $departmentId, $year);
    }

    /**
     * Generate a unique employee ID, e.g. E20260224
     */
    public static function generateEmployeeId(int $departmentId, ?int $year = null): string
    {
        return self::generate('staff', $departmentId, $year);
    }

    /**
     * Generate a unique ID for a student or staff member.
     * Format: {PREFIX}{YEAR}{DEPARTMENT}{SEQUENCE}
     * e.g. S20260224 (student), E20260224 (staff)
     *
     * The department segment encodes the *current* department, so an ID must be
     * reissued whenever a record moves to a different department. Use
     * {@see StudentNumberService::reissueForDepartment()} for that case.
     *
     * @param  string  $type  'student' or 'staff'
     */
    public static function generate(string $type, int $departmentId, ?int $year = null): string
    {
        $prefix = $type === 'student' ? 'S' : 'E';
        $field = $type === 'student' ? 'student_number' : 'employee_number';

        $allowedFields = ['student_number', 'employee_number'];
        if (! in_array($field, $allowedFields, true)) {
            throw new \InvalidArgumentException('Invalid field for ID generation');
        }

        $idPrefix = $prefix.self::yearSegment($year).self::departmentSegment($departmentId);

        return DB::transaction(function () use ($idPrefix, $field, $type) {
            $prefixLength = strlen($idPrefix);

            // Sequence is the remainder after the prefix, so any width is read
            // correctly (a fixed-width substring breaks once a series passes 99).
            $maxSeq = Profile::query()
                ->where($field, 'like', $idPrefix.'%')
                ->lockForUpdate()
                ->pluck($field)
                ->map(fn ($number) => (int) substr((string) $number, $prefixLength))
                ->filter(fn ($sequence) => $sequence > 0)
                ->max();

            if ($type === 'student') {
                $maxUserSeq = User::query()
                    ->where($field, 'like', $idPrefix.'%')
                    ->lockForUpdate()
                    ->pluck($field)
                    ->map(fn ($number) => (int) substr((string) $number, $prefixLength))
                    ->filter(fn ($sequence) => $sequence > 0)
                    ->max();

                $maxSeq = max($maxSeq ?? 0, $maxUserSeq ?? 0);
            }

            $nextSeq = ($maxSeq ?? 0) + 1;

            return $idPrefix.str_pad(
                (string) $nextSeq,
                max(self::SEQUENCE_MIN_WIDTH, strlen((string) $nextSeq)),
                '0',
                STR_PAD_LEFT
            );
        });
    }

    /**
     * The fixed-width department segment used inside generated IDs.
     */
    public static function departmentSegment(int $departmentId): string
    {
        return str_pad((string) $departmentId, 2, '0', STR_PAD_LEFT);
    }

    /**
     * The year segment used inside generated IDs.
     */
    public static function yearSegment(?int $year = null): string
    {
        return (string) ($year ?? (int) date('Y'));
    }
}
