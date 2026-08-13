<?php

namespace App\Console\Commands;

use App\Models\AcademicYear;
use Illuminate\Console\Command;

class CreateAcademicYear extends Command
{
    protected $signature = 'academic-year:create
                            {year? : The academic year (e.g. 2027). Defaults to next calendar year.}';

    protected $description = 'Create an academic year (Jan 1 – Dec 31) with default cohorts';

    public function handle(): int
    {
        $year = $this->argument('year') ?? now()->year + 1;

        if (! preg_match('/^\d{4}$/', $year)) {
            $this->error('Year must be a 4-digit number, e.g. 2027.');

            return self::FAILURE;
        }

        $year = (int) $year;

        $existing = AcademicYear::where('name', (string) $year)->first();
        if ($existing) {
            $this->warn("Academic year {$year} already exists.");

            return self::FAILURE;
        }

        $academicYear = AcademicYear::create([
            'name'       => (string) $year,
            'start_date' => "{$year}-01-01",
            'end_date'   => "{$year}-12-31",
            'is_current' => false,
        ]);

        $createdCohorts = $academicYear->generateDefaultCohorts();

        $this->info("Academic year {$year} created successfully.");
        $this->info("Generated {$createdCohorts} default cohorts.");

        return self::SUCCESS;
    }
}
