<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Reads and validates the structured parts of a student number.
 *
 * Student numbers follow the format "{PREFIX}{YEAR}{DEPARTMENT}{SEQUENCE}",
 * e.g. S20260417 is prefix "S", year 2026, department 4 and sequence 17. The
 * department segment is the reason numbers must be reissued on a transfer.
 */
class StudentNumberService
{
    public const PREFIX = 'S';

    /**
     * Split a student number into its parts.
     *
     * @return array{prefix: string, year: int, department_id: int, sequence: int}|null
     */
    public function parse(?string $number): ?array
    {
        if (! is_string($number) || ! preg_match('/^([A-Z])(\d{4})(\d{2})(\d+)$/', trim($number), $matches)) {
            return null;
        }

        return [
            'prefix' => $matches[1],
            'year' => (int) $matches[2],
            'department_id' => (int) $matches[3],
            'sequence' => (int) $matches[4],
        ];
    }

    /**
     * Does this number already encode the given department (and year)?
     *
     * A null $year skips the year comparison, which keeps students whose
     * enrolment date is unknown from being reissued on every edit. A number
     * that cannot be parsed never matches, because there is no way to confirm
     * it belongs to the department.
     */
    public function matches(?string $number, int $departmentId, ?int $year = null): bool
    {
        $parts = $this->parse($number);

        if ($parts === null) {
            return false;
        }

        if ($parts['department_id'] !== $departmentId) {
            return false;
        }

        if ($year !== null && $parts['year'] !== $year) {
            return false;
        }

        return true;
    }

    /**
     * The department a number belongs to, or null when it is not parseable.
     */
    public function departmentIdFor(?string $number): ?int
    {
        return $this->parse($number)['department_id'] ?? null;
    }

    /**
     * The sequence a number holds within its department series.
     */
    public function sequenceFor(?string $number): ?int
    {
        return $this->parse($number)['sequence'] ?? null;
    }
}
