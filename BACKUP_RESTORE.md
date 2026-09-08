# The Hive System - Database Backup & Restore

## Files
- `backup.sh` / `backup.bat` - standalone backup script
- `restore.sh` / `restore.bat` - standalone restore script
- `app/Console/Commands/BackupDatabase.php` - Laravel artisan command
- `app/Console/Commands/RestoreDatabase.php` - Laravel artisan command

## Usage

### Standalone Scripts

**Backup:**
```bash
# Linux/macOS
./backup.sh                    # single backup, no rotation
./backup.sh --daily            # daily backup, keeps last 7 days
./backup.sh --weekly           # weekly backup, keeps last 4 weeks
./backup.sh --monthly          # monthly backup, keeps last 12 months
./backup.sh --daily --compress # compress with gzip

# Windows
backup.bat                     # single backup, no rotation
backup.bat daily               # daily backup, keeps last 7 days
backup.bat weekly              # weekly backup, keeps last 4 weeks
backup.bat monthly             # monthly backup, keeps last 12 months
backup.bat daily compress      # compress with gzip
```

**Restore:**
```bash
# Linux/macOS
./restore.sh storage/backups/hbci_20260907_120000.sql
./restore.sh storage/backups/hbci_20260907_120000.sql.gz

# Windows
restore.bat storage\backups\hbci_20260907_120000.sql
restore.bat storage\backups\hbci_20260907_120000.sql.gz
```

### Laravel Artisan Commands

```bash
# Backup
php artisan hive:backup --type=daily --compress
php artisan hive:backup --type=weekly --compress
php artisan hive:backup --type=monthly --compress
php artisan hive:backup --type=manual --compress --keep=10

# Restore
php artisan hive:restore storage/backups/hbci_20260907_120000.sql.gz
php artisan hive:restore hbci_20260907_120000.sql.gz --force
```

### Scheduled Backups (Laravel)
The following schedules are already configured in `app/Console/Kernel.php`:
- Daily backup at 2:00 AM (keeps 7 days)
- Weekly backup on Sunday at 3:00 AM (keeps 4 weeks)
- Monthly backup on 1st at 4:00 AM (keeps 12 months)

Run `php artisan schedule:work` or use a system cron/scheduler to activate.

### Windows Task Scheduler
1. Open Task Scheduler
2. Create Basic Task named "Hive Daily Backup"
3. Trigger: Daily at 2:00 AM
4. Action: Start a program
   - Program: `backup.bat`
   - Arguments: `daily compress`
5. Check "Run whether user is logged on or not" + "Run with highest privileges"

## Backup Location
Backups are stored in `storage/backups/` with format:
`hbci_YYYYMMDD_HHMMSS.sql` (or `.sql.gz` if compressed)

## Restore Checklist
1. Stop the application (optional but recommended)
2. Run `restore.bat <backup_file>` or `php artisan hive:restore <backup_file>`
3. Verify data
4. Restart the application