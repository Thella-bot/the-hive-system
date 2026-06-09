<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;


class QueueWorkers extends Command
{
    protected $signature = 'queue:workers
        {--workers=4 : Number of worker processes to start}
        {--sleep=3 : Seconds to sleep when no job is available}
        {--tries=3 : Number of attempts for each job}
        {--timeout=60 : Max execution time (seconds) per job}
        {--queue= : Optional queue name(s) to listen on}
        {--daemon : Run as "--daemon" (uses queue:work --daemon) instead of looping queue:listen}
    ';

    protected $description = 'Start multiple queue workers for the configured queue connection';

    public function handle(): int
    {
        $workers = max(1, (int) $this->option('workers'));
        $sleep = (int) $this->option('sleep');
        $tries = max(1, (int) $this->option('tries'));
        $timeout = (int) $this->option('timeout');
        $queue = $this->option('queue');
        $daemon = $this->option('daemon');

        $queueArg = $queue ? (' --queue=' . $queue) : '';

        // On Windows, --daemon isn't as robust without a process manager.
        // We still support queue:work --daemon for users who explicitly want it.
        if (!empty($daemon)) {
            $cmdBase = sprintf(
                'php artisan queue:work%s --sleep=%d --tries=%d --timeout=%d --daemon',
                $queueArg,
                $sleep,
                $tries,
                $timeout
            );
        } else {
            $cmdBase = sprintf(
                'php artisan queue:listen%s --sleep=%d --tries=%d --timeout=%d',
                $queueArg,
                $sleep,
                $tries,
                $timeout
            );
        }

        $this->info("Starting {$workers} worker process(es) using: {$cmdBase}");
        $this->info('Stop with Ctrl+C in each terminal window/process as applicable.');

        // Spawn processes.
        // Windows: start "" /b <command>
        for ($i = 1; $i <= $workers; $i++) {
            $name = 'queue_worker_' . $i;
            $this->line('Launching: ' . $name);

            // The command must be run through cmd.exe.
            // Use "" title placeholder for start.
            $cmd = 'start "" /b cmd /c ' . escapeshellarg($cmdBase);
            // We use exec for best effort; if it fails, output will still guide manual start.
            @exec($cmd);
        }

        $this->info('Workers launched (best effort).');
        $this->info('Tip: also run `php artisan queue:failed` to inspect failures.');

        return Command::SUCCESS;
    }
}

