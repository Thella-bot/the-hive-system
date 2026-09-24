<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Enrollment;
use App\Models\User;
use Carbon\Carbon;

class AcademicYearHelper
{
    /**
     * Compute the academic year label for a given date.
     *
     * Jan-Mar enrollment → "YYYY"
     * Apr-Dec enrollment → "YYYY/YY"
     */
    public static function compute(Carbon $date): string
    {
        $year = (int) $date->year;
        $month = (int) $date->month;

        if ($month <= 3) {
            return (string) $year;
        }

        return $year . '/' . substr((string) ($year + 1), -2);
    }

    /**
     * Compute academic year from an Enrollment record.
     */
    public static function computeFromEnrollment(Enrollment $enrollment): string
    {
        $student = $enrollment->student;
        if ($student && $student->profile?->enrollment_date) {
            return self::compute(Carbon::parse($student->profile->enrollment_date));
        }

        return $enrollment->academic_year;
    }

    /**
     * Compute academic year from a User (student).
     */
    public static function computeFromStudent(User $student): string
    {
        $enrollmentDate = $student->profile?->enrollment_date;
        if ($enrollmentDate) {
            return self::compute(Carbon::parse($enrollmentDate));
        }

        return $student->profile?->cohort?->academicYear?->name ?? (string) now()->year;
    }

    /**
     * Group enrollments by computed academic year.
     */
    public static function groupEnrollments($enrollments): \Illuminate\Support\Collection
    {
        return $enrollments->groupBy(function (Enrollment $enrollment) {
            return self::computeFromEnrollment($enrollment);
        });
    }
}