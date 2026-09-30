<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Services\StudentNumberService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class StudentNumberServiceTest extends TestCase
{
    private StudentNumberService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new StudentNumberService;
    }

    public function test_it_parses_a_student_number_into_its_parts(): void
    {
        $parts = $this->service->parse('S20260417');

        $this->assertSame([
            'prefix' => 'S',
            'year' => 2026,
            'department_id' => 4,
            'sequence' => 17,
        ], $parts);
    }

    public function test_it_parses_a_sequence_longer_than_two_digits(): void
    {
        $parts = $this->service->parse('S202604100');

        $this->assertSame(100, $parts['sequence']);
    }

    #[DataProvider('unparseableNumbers')]
    public function test_it_returns_null_for_numbers_it_cannot_read(?string $number): void
    {
        $this->assertNull($this->service->parse($number));
    }

    public static function unparseableNumbers(): array
    {
        return [
            'null' => [null],
            'empty' => [''],
            'legacy format' => ['STU-2026-001'],
            'missing sequence' => ['S202604'],
            'letter in the department segment' => ['S2026AB17'],
        ];
    }

    public function test_a_number_matches_its_own_department_and_year(): void
    {
        $this->assertTrue($this->service->matches('S20260417', 4, 2026));
    }

    public function test_a_number_does_not_match_a_different_department(): void
    {
        $this->assertFalse($this->service->matches('S20260417', 2, 2026));
    }

    public function test_a_number_does_not_match_a_different_year(): void
    {
        $this->assertFalse($this->service->matches('S20260417', 4, 2023));
    }

    public function test_a_null_year_skips_the_year_comparison(): void
    {
        $this->assertTrue($this->service->matches('S20260417', 4, null));
    }

    public function test_an_unreadable_number_never_matches(): void
    {
        $this->assertFalse($this->service->matches('STU-2026-001', 4, 2026));
    }

    public function test_it_exposes_the_department_and_sequence(): void
    {
        $this->assertSame(4, $this->service->departmentIdFor('S20260417'));
        $this->assertSame(17, $this->service->sequenceFor('S20260417'));
        $this->assertNull($this->service->departmentIdFor('nope'));
        $this->assertNull($this->service->sequenceFor(null));
    }
}
