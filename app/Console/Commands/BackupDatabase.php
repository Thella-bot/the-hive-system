<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class BackupDatabase extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'hive:backup 
        {--type=daily : Backup type (daily, weekly, monthly, manual)}
        {--compress : Compress the backup with gzip}
        {--keep=N : Number of backups to keep (overrides type default)}';

    /**
     * The console command description.
     */
    protected $description = 'Create a database backup with rotation.';

    /**
     * Days to keep based on backup type.
     */
    protected array $retentionMap = [
        'daily'  => 7,
        'weekly' => 28,
        'monthly' => 365,
        'manual' => 0,
    ];

    public function handle(): int
    {
        $type = $this->option('type');
        $compress = $this->option('compress');
        $keep = $this->option('keep');

        $config = config('database.connections.' . config('database.default'));
        if (! $config || $config['driver'] !== 'mysql') {
            $this->error('Backup currently only supports MySQL databases.');
            return self::FAILURE;
        }

        $backupDir = storage_path('backups');
        if (! File::isDirectory($backupDir)) {
            File::makeDirectory($backupDir, 0755, true);
        }

        $timestamp = now()->format('Ymd_His');
        $db = $config['database'];
        $backupFile = "{$backupDir}/{$db}_{$timestamp}.sql";

        // Build mysqldump command
        $args = [
            'mysqldump',
            '--single-transaction',
            '--routines',
            '--events',
            '--triggers',
            '-h' . ($config['host'] ?? '127.0.0.1'),
            '-P' . ($config['port'] ?? '3306'),
            '-u' . ($config['username'] ?? 'root'),
        ];

        if (! empty($config['password'])) {
            $args[] = '-p' . $config['password'];
        }

        $args[] = $db;
        $args[] = '>';
        $args[] = $backupFile;

        $this->info("Dumping database...");
        shell_exec(implode(' ', array_map('escapeshellarg', $args)));

        if (! File::exists($backupFile)) {
            $this->error("Backup failed - file not created.");
            return self::FAILURE;
        }

        $size = filesize($backupFile);
        $this->info("Backup created: {$backupFile} (" . $this->formatBytes($size) . ")");

        // Compress if requested
        if ($compress) {
            $this->info("Compressing...");
            $gzFile = $backupFile . '.gz';
            shell_exec("gzip -f " . escapeshellarg($backupFile));
            $backupFile = $gzFile;
            $this->info("Compressed: {$backupFile} (" . $this->formatBytes(filesize($backupFile)) . ")");
        }

        // Rotate old backups
        $keepDays = $keep ?? ($this->retentionMap[$type] ?? 0);
        if ($keepDays > 0) {
            $this->info("Rotating backups (keeping last {$keepDays} days)...");
            $files = File::files($backupDir);
            $cutoff = now()->subDays($keepDays);
            $deleted = 0;

            foreach ($files as $file) {
                if (Str::startsWith($file->getFilename(), $db . '_') && $file->getCTime() < $cutoff->getTimestamp()) {
                    File::delete($file->getPathname());
                    $deleted++;
                }
            }

            $this->info("Deleted {$deleted} old backups.");
        }

        // List current backups
        $this->newLine();
        $this->info("Current backups:");
        $files = File::files($backupDir);
        usort($files, fn($a, $b) => $b->getCTime() <=> $a->getCTime());

        foreach (array_slice($files, 0, 10) as $file) {
            $this->line("  {$file->getFilename()} (" . $this->formatBytes(filesize($file->getPathname())) . ")");
        }

        $this->newLine();
        $this->info("Backup complete.");

        return self::SUCCESS;
    }

    protected function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = $bytes > 0 ? floor(log($bytes) / log(1024)) : 0;
        $pow = min($pow, count($units) - 1);

        return round($bytes / pow(1024, $pow), 2) . ' ' . $units[$pow];
    }
}