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

class ImportUsersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private string $filePath;

    public function __construct(string $filePath)
    {
        $this->filePath = $filePath;
    }

    public function handle(): void
    {
        $contents = Storage::get($this->filePath);
        $rows = array_map('str_getcsv', explode("\n", $contents));
        array_shift($rows); // remove header row (if any)

        foreach ($rows as $row) {
            if (count($row) < 4) continue; // validate
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

            // Send email (implement a Mailable class)
            // Mail::to($user)->send(new \App\Mail\WelcomeMail($user, $password));
        }

        Storage::delete($this->filePath);
    }
}