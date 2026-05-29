<?php

namespace App\Jobs;

use App\Models\User;
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
            $rows = array_map('str_getcsv', explode("\n", $contents));
            array_shift($rows); // remove header row

            // Step 1: Validate all rows before starting the transaction
            $this->validateRows($rows);

            if ($this->failureCount > 0) {
                throw new \Exception("Validation errors occurred during import.");
            }

            // Step 2: If validation passes, create users within a transaction
            DB::transaction(function () use ($rows) {
                foreach ($rows as $row) {
                     if (empty(array_filter($row))) { continue; }

                    if (count($row) < 4) {
                        $this->failureCount++;
                        $this->errors[] = 'Row ' . (array_search($row, $rows) + 2) . ': Expected at least 4 columns (first_name, last_name, email, role).';
                        continue;
                    }

                    [$firstName, $lastName, $email, $role] = $row;

                    $password = Str::random(10);
                    $user = User::create([
                        'name' => $firstName . ' ' . $lastName,
                        'email' => $email,
                        'password' => Hash::make($password),
                        'email_verified_at' => now(),
                        'approved_at' => now(),
                    ]);
                    $user->assignRole($role);

                    SendWelcomeEmail::dispatch($user, $password);
                    $this->successCount++;
                }
            });
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
                'has_job_error' => !is_null($jobError),
            ]);

            $this->user->notify(new ImportCompleted($this->successCount, $this->failureCount, $this->errors, $jobError));
            Storage::delete($this->filePath);
        }
    }

    private function validateRows(array $rows): void
    {
        foreach ($rows as $index => $row) {
            if (empty(array_filter($row))) { // Skip empty rows
                continue;
            }

            $validator = Validator::make($row, [
                '0' => 'required|string',
                '1' => 'required|string',
                '2' => 'required|email|unique:users,email',
                '3' => 'required|string|exists:roles,name',
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