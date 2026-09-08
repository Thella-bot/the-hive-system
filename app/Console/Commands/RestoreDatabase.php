<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class RestoreDatabase extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'hive:restore
        {backup : Backup file name or path (e.g. hbci_20260907_120000.sql.gz)}
        {--force : Skip confirmation prompt}';

    /**
     * The console command description.
     */
    protected $description = 'Restore database from a backup file.';

    public function handle(): int
    {
        $backup = $this->argument('backup');
        $force = $this->option('force');

        $config = config('database.connections.' . config('database.default'));
        if (! $config || $config['driver'] !== 'mysql') {
            $this->error('Restore currently only supports MySQL databases.');
            return self::FAILURE;
        }

        $backupDir = storage_path('backups');
        $backupPath = $backup;

        if (! Str::startsWith($backupPath, '/') && ! Str::startsWith($backupPath, storage_path())) {
            $backupPath = "{$backupDir}/{$backupPath}";
        }

        if (! File::exists($backupPath)) {
            $this->error("Backup file not found: {$backupPath}");
            $this->info("Available backups:");
            $this->listBackups($backupDir, $config['database']);
            return self::FAILURE;
        }

        if (! $force) {
            if (! $this->confirm("WARNING: This will REPLACE the current database '{$config['database']}'. Continue?")) {
                $this->info("Aborted.");
                return self::FAILURE;
            }
        }

        $sqlFile = $backupPath;
        $decompressed = false;

        // Decompress if gzipped
        if (Str::endsWith($backupPath, '.gz')) {
            $sqlFile = substr($backupPath, 0, -3);
            $this->info("Decompressing...");
            shell_exec("gzip -dc " . escapeshellarg($backupPath) . " > " . escapeshellarg($sqlFile));
            $decompressed = true;
        }

        // Build mysql command
        $args = [
            'mysql',
            '-h' . ($config['host'] ?? '127.0.0.1'),
            '-P' . ($config['port'] ?? '3306'),
            '-u' . ($config['username'] ?? 'root'),
        ];

        if (! empty($config['password'])) {
            $args[] = '-p' . $config['password'];
        }

        $args[] = $config['database'];
        $args[] = '<';
        $args[] = $sqlFile;

        $this->info("Restoring database...");
        $output = shell_exec(implode(' ', array_map('escapeshellarg', $args)));

        if ($decompressed) {
            File::delete($sqlFile);
        }

        $this->info("Restore complete.");

        return self::SUCCESS;
    }

    protected function listBackups(string $dir, string $db): void
    {
        if (! File::isDirectory($dir)) {
            $this->line("  (no backup directory)");
            return;
        }

        $files = collect(File::files($dir))
            ->filter(fn($f) => Str::startsWith($f->getFilename(), $db . '_'))
            ->sortByDesc(fn($f) => $f->getCTime())
            ->values();

        if ($files->isEmpty()) {
            $this->line("  (no backups found)");
            return;
        }

        foreach ($files as $file) {
            $this->line("  {$file->getFilename()} (" . $this->formatBytes(filesize($file->getPathname())) . ")");
        }
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