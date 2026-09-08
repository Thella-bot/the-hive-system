@echo off
REM ============================================================================
REM The Hive System - Database Restore Script (Windows)
REM Usage: restore.bat <backup_file.sql[.gz]>
REM ============================================================================

setlocal enabledelayedexpansion

set "SCRIPT_DIR=%~dp0"
cd /d "%SCRIPT_DIR%"

if "%~1"=="" (
    echo Usage: restore.bat ^<backup_file.sql[.gz]^>
    echo.
    echo Available backups:
    dir /b "storage\backups\*.sql*" 2>nul || echo   (none found)
    exit /b 1
)

set "BACKUP_FILE=%~1"
if "%BACKUP_FILE:~1,1%" neq ":" (
    set "BACKUP_FILE=%SCRIPT_DIR%%BACKUP_FILE"
)

if not exist "%BACKUP_FILE%" (
    echo ERROR: Backup file not found: %BACKUP_FILE%
    echo.
    echo Available backups:
    dir /b "storage\backups\*.sql*" 2>nul || echo   (none found)
    exit /b 1
)

REM Read DB credentials from .env
for /f "tokens=1,* delims==" %%a in ('type ".env" 2^>nul ^| findstr /i "^DB_") do (
    set "%%a=%%b"
)
if "%DB_HOST%"=="" set "DB_HOST=127.0.0.1"
if "%DB_PORT%"=="" set "DB_PORT=3306"
if "%DB_DATABASE%"=="" set "DB_DATABASE=hbci"
if "%DB_USERNAME%"=="" set "DB_USERNAME=root"

echo =============================================
echo   The Hive System - Database Restore
echo =============================================
echo Database:  %DB_DATABASE%
echo Backup:    %BACKUP_FILE%
echo.

REM Confirm destructive operation
set /p "CONFIRM=WARNING: This will REPLACE the current database. Continue? (y/N) "
if /i not "%CONFIRM%"=="y" (
    echo Aborted.
    exit /b 0
)

REM Decompress if needed
set "SQL_FILE=%BACKUP_FILE%"
if /i "%BACKUP_FILE:~-3%"==".gz" (
    echo Decompressing...
    powershell -Command "Expand-Archive -Path '%BACKUP_FILE%' -DestinationPath '%SCRIPT_DIR%' -Force"
    set "SQL_FILE=%BACKUP_FILE%.sql"
)

REM Restore
echo Restoring database...
if defined DB_PASSWORD (
    mysql -p%DB_PASSWORD% -h%DB_HOST% -P%DB_PORT% -u%DB_USERNAME% %DB_DATABASE% < "%SQL_FILE%"
) else (
    mysql -h%DB_HOST% -P%DB_PORT% -u%DB_USERNAME% %DB_DATABASE% < "%SQL_FILE%"
)

REM Clean up decompressed file if we created it
if /i "%BACKUP_FILE:~-3%"==".gz" (
    del /f "%SQL_FILE%" 2>nul
)

echo.
echo Restore complete.
endlocal