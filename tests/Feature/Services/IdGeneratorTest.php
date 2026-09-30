<?php

declare(strict_types=1);

namespace Tests\Feature\Services;

use App\Models\Profile;
use App\Models\User;
use App\Services\IdGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IdGeneratorTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_builds_a_number_from_the_department_and_year(): void
    {
        $this->assertSame('S20260401', IdGenerator::generateStudentId(4, 2026));
    }

    public function test_it_defaults_to_the_current_year(): void
    {
        $this->assertSame('S'.date('Y').'0401', IdGenerator::generateStudentId(4));
    }

    public function test_the_sequence_increments_within_a_series(): void
    {
        // The generator reads the highest number in use, so each call has to be
        // followed by the record actually claiming it.
        $this->claim(IdGenerator::generateStudentId(4, 2026));
        $this->claim(IdGenerator::generateStudentId(4, 2026));
        $this->claim(IdGenerator::generateStudentId(4, 2026));

        $this->assertSame('S20260404', IdGenerator::generateStudentId(4, 2026));
    }

    public function test_each_department_has_an_independent_series(): void
    {
        $this->claim(IdGenerator::generateStudentId(2, 2026));
        $this->claim(IdGenerator::generateStudentId(4, 2026));

        $this->assertSame('S20260202', IdGenerator::generateStudentId(2, 2026));
        $this->assertSame('S20260402', IdGenerator::generateStudentId(4, 2026));
    }

    public function test_each_year_has_an_independent_series(): void
    {
        $this->claim(IdGenerator::generateStudentId(4, 2026));

        $this->assertSame('S20230401', IdGenerator::generateStudentId(4, 2023));
        $this->assertSame('S20260402', IdGenerator::generateStudentId(4, 2026));
    }

    public function test_it_accounts_for_numbers_already_taken_on_the_user_record(): void
    {
        User::factory()->create(['student_number' => 'S20260407']);

        $this->assertSame('S20260408', IdGenerator::generateStudentId(4, 2026));
    }

    public function test_it_continues_past_a_two_digit_sequence(): void
    {
        Profile::factory()->create(['student_number' => 'S20260499']);

        $this->assertSame('S202604100', IdGenerator::generateStudentId(4, 2026));
    }

    public function test_it_pads_departments_to_two_digits(): void
    {
        $this->assertStringStartsWith('S2026', IdGenerator::generateStudentId(4, 2026));
        $this->assertStringContainsString('04', IdGenerator::generateStudentId(4, 2026));
    }

    /**
     * Persist a generated number so the next call sees it as taken.
     */
    protected function claim(string $studentNumber): void
    {
        Profile::factory()->create(['student_number' => $studentNumber]);
    }
}
