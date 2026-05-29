<?php

namespace App\Console\Commands;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class SendAnniversaryNotifications extends Command
{
    protected $signature = 'hbci:anniversary-notifications';

    public function handle(): int
    {
        $today = now();

        // Birthday notifications
        $usersWithBirthday = User::whereHas('profile', function ($q) use ($today) {
            $q->whereMonth('date_of_birth', $today->month)
              ->whereDay('date_of_birth', $today->day);
        })->with('profile')->get();

        foreach ($usersWithBirthday as $user) {
            $colleagues = User::where('id', '!=', $user->id)
                ->whereRelation('roles', 'name', '!=', 'student')
                ->get();
            Notification::send($colleagues, new \App\Notifications\BirthdayNotification($user));
        }

        // Work anniversary notifications
        $usersWithAnniversary = User::whereHas('profile', function ($q) use ($today) {
            $q->whereMonth('hire_date', $today->month)
              ->whereDay('hire_date', $today->day);
        })->with('profile')->get();

        foreach ($usersWithAnniversary as $user) {
            $years = $user->profile->hire_date->diffInYears($today);
            $colleagues = User::where('id', '!=', $user->id)
                ->whereRelation('roles', 'name', '!=', 'student')
                ->get();
            Notification::send($colleagues, new \App\Notifications\WorkAnniversaryNotification($user, $years));
        }

        $this->info('Anniversary notifications sent.');
        return self::SUCCESS;
    }
}
