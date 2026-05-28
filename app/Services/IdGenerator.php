<?php

namespace App\Services;

use App\Models\Profile;

class IdGenerator
{
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
        $year = date('y');
        $departmentCode = str_pad($departmentId, 2, '0', STR_PAD_LEFT);

        $idPrefix = "{$prefix}{$year}{$departmentCode}";

        $field = ($type === 'student' ? 'student_number' : 'employee_number');

        $latestProfile = Profile::where($field, 'like', "{$idPrefix}%")
            ->orderBy($field, 'desc')
            ->first();

        $newNumber = 1;
        if ($latestProfile) {
            $latestId = $latestProfile->$field;
            $lastNumber = (int) substr($latestId, -4);
            $newNumber = $lastNumber + 1;
        }

        return $idPrefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
}