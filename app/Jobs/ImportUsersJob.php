<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Cohort;
use App\Models\Programme;
use App\Models\User;
use App\Notifications\ImportCompleted;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ImportUsersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $filePath;

    protected User $user;

    protected int $successCount = 0;

    protected int $failureCount = 0;

    protected int $duplicateCount = 0;

    protected int $skippedCount = 0;

    protected array $errors = [];

    protected array $seenEmails = [];

    protected array $seenNationalIds = [];

    protected array $seenNationalIdUsers = [];

    protected array $seenStudentNumbers = [];

    public function __construct(string $filePath, User $user)
    {
        $this->filePath = $filePath;
        $this->user = $user;
    }

    public function handle(): void
    {
        $jobError = null;

        try {
            Log::info('import_users.started', [
                'file_path' => $this->filePath,
                'user_id' => $this->user->id,
            ]);

            $contents = Storage::get($this->filePath);

            $rows = [];
            $handle = fopen('php://memory', 'r+');
            fwrite($handle, $contents);
            rewind($handle);
            while (($row = fgetcsv($handle)) !== false) {
                $rows[] = $row;
            }
            fclose($handle);

            array_shift($rows);

            $this->validateRows($rows);

            if ($this->failureCount > 0) {
                throw new \Exception('Validation errors occurred during import.');
            }

            DB::transaction(function () use ($rows) {
                foreach ($rows as $index => $row) {
                    if (empty(array_filter($row))) {
                        continue;
                    }

                    while (count($row) < 18) {
                        $row[] = null;
                    }

                    $email = strtolower(trim((string) ($row[9] ?? '')));
                    $fullName = trim((string) ($row[2] ?? ''));
                    $nationalId = trim((string) ($row[3] ?? ''));
                    $dateOfBirth = trim((string) ($row[4] ?? ''));
                    $gender = trim((string) ($row[5] ?? ''));
                    $cellPhone = trim((string) ($row[7] ?? ''));
                    $address = trim((string) ($row[10] ?? ''));
                    $emergencyContactName = trim((string) ($row[12] ?? ''));
                    $emergencyContactPhone = trim((string) ($row[13] ?? ''));
                    $programmeName = trim((string) ($row[14] ?? ''));
                    $intakeDate = trim((string) ($row[16] ?? ''));
                    $csvStudentNumber = trim((string) ($row[17] ?? ''));

                    $duplicateReason = null;
                    $duplicateAction = 'skip';

                    if ($email && in_array($email, $this->seenEmails, true)) {
                        $duplicateReason = 'Duplicate email in CSV: '.$email;
                        $duplicateAction = 'skip';
                    } elseif ($nationalId && in_array($nationalId, $this->seenNationalIds, true)) {
                        $duplicateReason = 'Duplicate national ID in CSV: '.$nationalId;
                        $duplicateAction = 'merge';
                    } elseif ($csvStudentNumber && ! in_array($csvStudentNumber, ['N/A', 'None', ''], true) && in_array($csvStudentNumber, $this->seenStudentNumbers, true)) {
                        $duplicateReason = 'Duplicate student number in CSV: '.$csvStudentNumber;
                        $duplicateAction = 'skip';
                    }

                    if ($duplicateReason) {
                        $this->duplicateCount++;
                        $this->errors[] = 'Row '.($index + 2).': '.$duplicateReason;

                        if ($duplicateAction === 'merge' && isset($this->seenNationalIdUsers[$nationalId])) {
                            $user = $this->seenNationalIdUsers[$nationalId];
                            $this->applyStudentData($user, $fullName, $email, $gender, $nationalId, $dateOfBirth, $cellPhone, $address, $emergencyContactName, $emergencyContactPhone, $programmeName, $intakeDate, $csvStudentNumber, $index + 2);
                            $this->successCount++;
                        }

                        continue;
                    }

                    if ($email) {
                        $this->seenEmails[] = $email;
                    }
                    if ($nationalId) {
                        $this->seenNationalIds[] = $nationalId;
                    }
                    if ($csvStudentNumber && ! in_array($csvStudentNumber, ['N/A', 'None', ''], true)) {
                        $this->seenStudentNumbers[] = $csvStudentNumber;
                    }

                    $existingUser = User::where('email', $email)->first();

                    if ($existingUser) {
                        $this->applyStudentData($existingUser, $fullName, $email, $gender, $nationalId, $dateOfBirth, $cellPhone, $address, $emergencyContactName, $emergencyContactPhone, $programmeName, $intakeDate, $csvStudentNumber, $index + 2);
                        $this->seenNationalIdUsers[$nationalId] = $existingUser;
                        $this->successCount++;
                    } else {
                        $password = Str::random(10);

                        $user = User::create([
                            'name' => $fullName,
                            'email' => $email,
                            'password' => Hash::make($password),
                            'email_verified_at' => now(),
                            'approved_at' => now(),
                        ]);

                        $user->syncRoles(['student']);

                        if ($nationalId) {
                            $user->national_id_number = $nationalId;
                            $user->save();
                        }

                        if ($gender) {
                            $user->gender = $gender;
                            $user->save();
                        }

                        $this->applyStudentData($user, $fullName, $email, $gender, $nationalId, $dateOfBirth, $cellPhone, $address, $emergencyContactName, $emergencyContactPhone, $programmeName, $intakeDate, $csvStudentNumber, $index + 2);

                        if ($nationalId) {
                            $this->seenNationalIdUsers[$nationalId] = $user;
                        }

                        SendWelcomeEmail::dispatch($user);

                        $this->successCount++;
                    }
                }
            });

            Storage::delete($this->filePath);
        } catch (\Exception $e) {
            $jobError = $e->getMessage();
            Log::error('import_users.failed', [
                'file_path' => $this->filePath,
                'user_id' => $this->user->id,
                'error' => $jobError,
                'exception' => get_class($e),
            ]);
        } finally {
            Log::info('import_users.completed', [
                'file_path' => $this->filePath,
                'user_id' => $this->user->id,
                'success_count' => $this->successCount,
                'failure_count' => $this->failureCount,
                'duplicate_count' => $this->duplicateCount,
                'skipped_count' => $this->skippedCount,
                'has_job_error' => ! is_null($jobError),
            ]);

            $this->user->notify(new ImportCompleted(
                $this->successCount,
                $this->failureCount,
                $this->duplicateCount,
                $this->skippedCount,
                $this->errors,
                $jobError
            ));
        }
    }

    private function applyStudentData(User $user, string $fullName, string $email, string $gender, string $nationalId, string $dateOfBirth, string $cellPhone, string $address, string $emergencyContactName, string $emergencyContactPhone, string $programmeName, string $intakeDate, string $csvStudentNumber, int $rowIndex = 0): void
    {
        $user->name = $fullName;
        $user->email = $email;
        $user->approved_at = now();

        if ($gender) {
            $user->gender = $gender;
        }

        if ($nationalId) {
            $user->national_id_number = $nationalId;
        }

        $user->save();

        $studentNumber = $csvStudentNumber;
        if (! $studentNumber || in_array($studentNumber, ['N/A', 'None'], true)) {
            $studentNumber = $user->student_number;
        }

        if ($studentNumber && ! str_starts_with($studentNumber, 'S')) {
            $studentNumber = 'S'.$studentNumber;
        }

        $programme = null;
        $departmentId = null;
        if ($programmeName) {
            $programme = Programme::where('name', $programmeName)->first();
            if ($programme) {
                $departmentId = $programme->department_id;
                $user->programme()->associate($programme);
                $user->save();
            }
        }

        $cohort = null;
        $enrollmentDate = null;
        if ($intakeDate) {
            try {
                $enrollmentDate = Carbon::createFromFormat('m/d/Y', $intakeDate);
                $cohort = $this->findCohortForDate($enrollmentDate, $departmentId);
            } catch (\Exception $e) {
                Log::warning('import_users.invalid_intake_date', [
                    'row' => $index + 2,
                    'date' => $intakeDate,
                ]);
            }
        }

        $profileData = [
            'date_of_birth' => $dateOfBirth ?: null,
            'phone' => $cellPhone ?: null,
            'address' => $address ?: null,
            'emergency_contact_name' => $emergencyContactName ?: null,
            'emergency_contact_phone' => $emergencyContactPhone ?: null,
            'enrollment_date' => $enrollmentDate ? $enrollmentDate->format('Y-m-d') : null,
        ];

        if ($cohort) {
            $profileData['cohort_id'] = $cohort->id;
        }

        if ($studentNumber) {
            $profileData['student_number'] = $studentNumber;
            $user->student_number = $studentNumber;
            $user->save();
        }

        $user->profile()->updateOrCreate(
            ['profileable_id' => $user->id, 'profileable_type' => User::class],
            $profileData
        );
    }

    private function findCohortForDate(Carbon $date, ?int $departmentId): ?Cohort
    {
        $query = Cohort::whereDate('start_date', '<=', $date)
            ->whereDate('end_date', '>=', $date);

        if ($departmentId) {
            $query->where('department_id', $departmentId);
        }

        return $query->first();
    }

    private function validateRows(array $rows): void
    {
        foreach ($rows as $index => $row) {
            if (empty(array_filter($row))) {
                continue;
            }

            $trimmedRow = array_map(function ($value) {
                return is_string($value) ? trim($value) : $value;
            }, $row);

            $validator = Validator::make($trimmedRow, [
                '9' => 'nullable|email',
            ]);

            if ($validator->fails()) {
                $this->failureCount++;
                $rowNumber = $index + 2;
                $firstError = $validator->errors()->first();
                $this->errors[] = 'Row '.$rowNumber.': '.$firstError;

                Log::warning('import_users.row_validation_failed', [
                    'file_path' => $this->filePath,
                    'row' => $rowNumber,
                    'error' => $firstError,
                    'user_id' => $this->user->id,
                ]);
            }
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('import_users.failed_job', [
            'file_path' => $this->filePath,
            'user_id' => $this->user->id,
            'exception' => get_class($exception),
            'message' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}
