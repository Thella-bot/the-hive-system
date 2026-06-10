<?php

namespace App\Services;

use App\Models\Profile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class IdGenerator
{
    /**
     * Generate a unique student ID.
     */
    public static function generateStudentId(int $departmentId): string
    {
        return self::generate('student', $departmentId);
    }

    /**
     * Generate a unique employee ID.
     */
    public static function generateEmployeeId(int $departmentId): string
    {
        return self::generate('staff', $departmentId);
    }

    /**
     * Generate a unique ID for a student or staff member.
     *
     * @param string $type 'student' or 'staff'
     * @param int $departmentId
     * @return string
     */
    public static function generate(string $type, int $departmentId): string
    {
        $prefix = ($type === 'student' ? 'S' : 'E');
        $year = date('Y');
        $departmentCode = str_pad($departmentId, 2, '0', STR_PAD_LEFT);

        $idPrefix = "{$prefix}{$year}{$departmentCode}";
        $field = ($type === 'student' ? 'student_number' : 'employee_number');

        return DB::transaction(function () use ($idPrefix, $field, $type, $departmentId) {
            $newNumber = 1;

            // Lock rows to prevent race conditions during concurrent requests
            $lockedIds = [];

            // Check Profile table first - use shared lock for consistent read
            $latestProfile = Profile::where($field, 'like', "{$idPrefix}%")
                ->lockForUpdate()
                ->orderBy($field, 'desc')
                ->first();

            if ($latestProfile) {
                $lastNumber = (int) substr($latestProfile->$field, -3);
                $newNumber = max($newNumber, $lastNumber + 1);
                $lockedIds[] = $latestProfile->id;
            }

            // Also check User table for student_number
            if ($type === 'student' && \Illuminate\Support\Facades\Schema::hasColumn('users', 'student_number')) {
                $latestUser = \App\Models\User::where('student_number', 'like', "{$idPrefix}%")
                    ->lockForUpdate()
                    ->orderBy('student_number', 'desc')
                    ->first();

                if ($latestUser) {
                    $lastNumber = (int) substr($latestUser->student_number, -3);
                    $newNumber = max($newNumber, $lastNumber + 1);
                    $lockedIds[] = $latestUser->id;
                }
            }

            $generatedId = $idPrefix . str_pad($newNumber, 3, '0', STR_PAD_LEFT);

            // Quick check without loop - verify uniqueness within transaction
            if (Profile::where($field, $generatedId)->exists()
                || ($type === 'student' && \App\Models\User::where($field, $generatedId)->exists())
            ) {
                // Fallback to incrementing counter if collision detected
                $maxNumber = Profile::where($field, 'like', "{$idPrefix}%")
                    ->selectRaw("MAX(CAST(SUBSTRING({$field}, -3) AS UNSIGNED)) as max_num")
                    ->value('max_num') ?? 0;

                if ($type === 'student') {
                    $maxUserNumber = \App\Models\User::where('student_number', 'like', "{$idPrefix}%")
                        ->selectRaw("MAX(CAST(SUBSTRING(student_number, -3) AS UNSIGNED)) as max_num")
                        ->value('max_num') ?? 0;
                    $maxNumber = max($maxNumber, $maxUserNumber);
                }

                $newNumber = ($maxNumber ?: 0) + 1;
                $generatedId = $idPrefix . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
            }

            return $generatedId;
        });
    }
}