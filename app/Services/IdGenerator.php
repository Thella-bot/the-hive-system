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

            // Check Profile table first
            $latestProfile = Profile::where($field, 'like', "{$idPrefix}%")
                ->orderBy($field, 'desc')
                ->first();

            if ($latestProfile) {
                $lastNumber = (int) substr($latestProfile->$field, -4);
                $newNumber = max($newNumber, $lastNumber + 1);
            }

            // Also check User table for student_number
            if ($type === 'student' && \Illuminate\Support\Facades\Schema::hasColumn('users', 'student_number')) {
                $latestUser = \App\Models\User::where('student_number', 'like', "{$idPrefix}%")
                    ->orderBy('student_number', 'desc')
                    ->first();

                if ($latestUser) {
                    $lastNumber = (int) substr($latestUser->student_number, -4);
                    $newNumber = max($newNumber, $lastNumber + 1);
                }
            }

            $generatedId = $idPrefix . str_pad($newNumber, 3, '0', STR_PAD_LEFT);

            // Ensure uniqueness - check both Profile and User tables for students
            while (Profile::where($field, $generatedId)->exists()
                || ($type === 'student' && \App\Models\User::where($field, $generatedId)->exists())
            ) {
                $newNumber++;
                $generatedId = $idPrefix . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
            }

            return $generatedId;
        });
    }
}