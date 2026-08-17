<?php

namespace App\Services;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class IdGenerator
{
    /**
     * Generate a unique student ID, e.g. S20260224
     */
    public static function generateStudentId(int $departmentId): string
    {
        return self::generate('student', $departmentId);
    }

    /**
     * Generate a unique employee ID, e.g. E20260224
     */
    public static function generateEmployeeId(int $departmentId): string
    {
        return self::generate('staff', $departmentId);
    }

    /**
     * Generate a unique ID for a student or staff member.
     * Format: {PREFIX}{YEAR}{DEPARTMENT}{SEQUENCE}
     * e.g. S20260224 (student), E20260224 (staff)
     *
     * @param string $type 'student' or 'staff'
     */
    public static function generate(string $type, int $departmentId): string
    {
        $prefix = $type === 'student' ? 'S' : 'E';
        $field = $type === 'student' ? 'student_number' : 'employee_number';
        $idPrefix = $prefix . date('Y') . str_pad($departmentId, 2, '0', STR_PAD_LEFT);

        $allowedFields = ['student_number', 'employee_number'];
        if (!in_array($field, $allowedFields, true)) {
            throw new \InvalidArgumentException('Invalid field for ID generation');
        }

        return DB::transaction(function () use ($idPrefix, $field, $type) {
            $maxSeq = Profile::where($field, 'like', "{$idPrefix}%")
                ->lockForUpdate()
                ->max(DB::raw("CAST(SUBSTRING(" . $field . ", -2) AS UNSIGNED)"));

            if ($type === 'student') {
                $maxUserSeq = User::where('student_number', 'like', "{$idPrefix}%")
                    ->lockForUpdate()
                    ->max(DB::raw("CAST(SUBSTRING(student_number, -2) AS UNSIGNED)"));

                $maxSeq = max($maxSeq ?? 0, $maxUserSeq ?? 0);
            }

            $nextSeq = ($maxSeq ?? 0) + 1;

            return $idPrefix . str_pad($nextSeq, 2, '0', STR_PAD_LEFT);
        });
    }
}