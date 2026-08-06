<?php

namespace App\Jobs;

use App\Models\Department;
use App\Models\Programme;
use App\Models\User;
use App\Services\IdGenerator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Notifications\ImportCompleted;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ImportUsersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $filePath;
    protected User $user;
    protected int $successCount = 0;
    protected int $failureCount = 0;
    protected array $errors = [];

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
                throw new \Exception("Validation errors occurred during import.");
            }

            DB::transaction(function () use ($rows) {
                foreach ($rows as $index => $row) {
                    if (empty(array_filter($row))) {
                        continue;
                    }

                    if (count($row) < 3) {
                        $this->failureCount++;
                        $this->errors[] = 'Row ' . ($index + 2) . ': Expected at least 3 columns (full_name, email, role).';
                        continue;
                    }

                    [$fullName, $email, $role] = $row;

                    $nameParts = explode(' ', trim($fullName), 2);
                    $firstName = $nameParts[0] ?? '';
                    $lastName = $nameParts[1] ?? '';

                    $dateOfBirth = $row[3] ?? null;
                    $phone = $row[4] ?? null;
                    $address = $row[5] ?? null;
                    $emergencyContactName = $row[6] ?? null;
                    $emergencyContactPhone = $row[7] ?? null;
                    $programmeName = $row[8] ?? null;
                    $yearOfStudy = $row[9] ?? null;
                    $intakeDate = $row[10] ?? null;
                    $existingStudentNumber = $row[11] ?? null;

                    $existingUser = User::where('email', $email)->first();

                    if ($existingUser) {
                        $user = $existingUser;
                        $user->name = $firstName . ' ' . $lastName;
                        $user->email = $email;
                        $user->approved_at = now();
                        $user->save();
                    } else {
                        $password = Str::random(10);
                        $user = User::create([
                            'name' => $firstName . ' ' . $lastName,
                            'email' => $email,
                            'password' => Hash::make($password),
                            'email_verified_at' => now(),
                            'approved_at' => now(),
                        ]);
                    }
                    $isNewUser = ! $existingUser;
                    $user->syncRoles([$role]);

                    if ($role === 'student') {
                        $this->createStudentProfile($user, $dateOfBirth, $phone, $address, $emergencyContactName, $emergencyContactPhone, $programmeName, $yearOfStudy, $intakeDate, $existingStudentNumber);
                    }

                    if ($isNewUser) {
                        SendWelcomeEmail::dispatch($user, $password);
                    }
                    $this->successCount++;
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
                'has_job_error' => ! is_null($jobError),
            ]);

            $this->user->notify(new ImportCompleted($this->successCount, $this->failureCount, $this->errors, $jobError));
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

    private function createStudentProfile(User $user, ?string $dateOfBirth, ?string $phone, ?string $address, ?string $emergencyContactName, ?string $emergencyContactPhone, ?string $programmeName, ?string $yearOfStudy, ?string $intakeDate, ?string $existingStudentNumber): void
    {
        $departmentId = null;
        $enrollmentDate = null;

        if (!empty($intakeDate)) {
            $enrollmentDate = $intakeDate;
        }

        if (!empty($programmeName)) {
            $programme = Programme::where('name', $programmeName)->first();
            if ($programme) {
                $departmentId = $programme->department_id;
                $user->programme()->associate($programme);
                $user->save();
            }
        }

        if (!$departmentId) {
            $defaultDept = Department::active()->first();
            if ($defaultDept) {
                $departmentId = $defaultDept->id;
            }
        }

        $profileData = [
            'date_of_birth' => $dateOfBirth,
            'phone' => $phone,
            'address' => $address,
            'emergency_contact_name' => $emergencyContactName,
            'emergency_contact_phone' => $emergencyContactPhone,
            'enrollment_date' => $enrollmentDate,
        ];

        if (!empty($existingStudentNumber)) {
            $user->student_number = $existingStudentNumber;
            $user->save();
        } elseif ($departmentId) {
            $user->student_number = IdGenerator::generateStudentId($departmentId);
            $user->save();
        }

        $user->profile()->create($profileData);
    }

    private function validateRows(array $rows): void
    {
        foreach ($rows as $index => $row) {
            if (empty(array_filter($row))) { // Skip empty rows
                continue;
            }

            $validator = Validator::make($row, [
                '0' => 'required|string',
                '1' => 'required|email',
                '2' => 'required|string|exists:roles,name',
            ]);

            if ($validator->fails()) {
                $this->failureCount++;
                $rowNumber = $index + 2;
                $firstError = $validator->errors()->first();
                $this->errors[] = 'Row ' . $rowNumber . ': ' . $firstError;

                Log::warning('import_users.row_validation_failed', [
                    'file_path' => $this->filePath,
                    'row' => $rowNumber,
                    'error' => $firstError,
                    'user_id' => $this->user->id,
                ]);
            }
        }
    }
}