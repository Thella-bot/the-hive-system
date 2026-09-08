@echo off
REM ============================================================================
REM The Hive System - Database Backup Script (Windows)
REM Usage: backup.bat [option]
REM Options:
REM   daily      Daily backup (keeps last 7 days)
REM   weekly     Weekly backup (keeps last 4 weeks)
REM   monthly    Monthly backup (keps last 12 months)
REM   manual     Single backup, no rotation (default)
REM   compress   Compress backup with gzip
REM   help       Show this help
REM ============================================================================

setlocal enabledelayedexpansion

set "SCRIPT_DIR=%~dp0"
cd /d "%SCRIPT_DIR%"

REM Read DB credentials from .env
for /f "tokens=1,* delims==" %%a in ('type ".env" 2^>nul ^| findstr /i "^DB_") do (
    set "%%a=%%b"
)

if "%DB_CONNECTION%"=="" set "DB_CONNECTION=mysql"
if "%DB_HOST%"=="" set "DB_HOST=127.0.0.1"
if "%DB_PORT%"=="" set "DB_PORT=3306"
if "%DB_DATABASE%"=="" set "DB_DATABASE=hbci"
if "%DB_USERNAME%"=="" set "DB_USERNAME=root"

set "BACKUP_DIR=%SCRIPT_DIR%storage\backups"
if not exist "%BACKUP_DIR%" mkdir "%BACKUP_DIR%"

set /a TIMESTAMP=20260907000000
for /f "tokens=1-4 delims=/:. " %%x in ('date /t') do (
    set /a TIMESTAMP=%%x%%y%%z
    set TIMESTAMP=%%x%%y%%z
)

REM Use PowerShell for timestamp
for /f "tokens=1" %%t in ('powershell -Command "Get-Date -Format yyyyMMdd_HHmmss"') do (
    set "TIMESTAMP=%%t"
)

set "BACKUP_FILE=%BACKUP_DIR%\%DB_DATABASE%_%TIMESTAMP%.sql"
set "MODE=%~1"
if "%MODE%"=="" set "MODE=manual"

echo =============================================
echo   The Hive System - Database Backup
echo =============================================
echo Database:  %DB_DATABASE%
echo Host:      %DB_HOST%:%DB_PORT%
echo Timestamp: %TIMESTAMP%
echo Mode:      %MODE%
echo.

REM Build mysqldump command
set "MYSQL_CMD=mysqldump"
if defined DB_PASSWORD (
    set "MYSQL_CMD=%MYSQL_CMD% -p%DB_PASSWORD%"
)
set "MYSQL_CMD=%MYSQL_CMD% -h%DB_HOST% -P%DB_PORT% -u%DB_USERNAME% --single-transaction --routines --events --triggers %DB_DATABASE% > "%BACKUP_FILE%""

echo Dumping database...
%MYSQL_CMD%

for %%A in ("%BACKUP_FILE%") do set "BACKUP_SIZE=%%~zA"
echo Backup created: %BACKUP_FILE% (%BACKUP_SIZE% bytes)

REM Compress if requested
if "%~2"=="compress" (
    echo Compressing...
    powershell -Command "Compress-Archive -Path '%BACKUP_FILE%' -DestinationPath '%BACKUP_FILE%.gz' -Force"
    del /f "%BACKUP_FILE%"
    set "BACKUP_FILE=%BACKUP_FILE%.gz"
    echo Compressed: %BACKUP_FILE%
)

REM Rotate old backups
echo.
echo Rotating old backups...
if "%MODE%"=="daily" (
    forfiles /p "%BACKUP_DIR%" /m "%DB_DATABASE%_*.sql*" /d -7 /c "cmd /c if @ISDIR==false del @PATH" 2>nul
    echo Kept last 7 daily backups.
) else if "%MODE%"=="weekly" (
    forfiles /p "%BACKUP_DIR%" /m "%DB_DATABASE%_*.sql*" /d -28 /c "cmd /c if @ISDIR==false del @PATH" 2>nul
    echo Kept last 4 weekly backups.
) else if "%MODE%"=="monthly" (
    forfiles /p "%BACKUP_DIR%" /m "%DB_DATABASE%_*.sql*" /d -365 /c "cmd /c if @ISDIR==false del @PATH" 2>nul
    echo Kept last 12 monthly backups.
) else (
    echo No rotation for manual backup.
)

REM List current backups
echo.
echo Current backups in %BACKUP_DIR%:
dir /b "%BACKUP_DIR%\%DB_DATABASE%_*.sql*" 2>nul || echo   (none found)

echo.
echo Backup complete.
endlocal