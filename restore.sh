#!/usr/bin/env bash
# ============================================================================
# The Hive System - Database Restore Script
# Usage: ./restore.sh <backup_file.sql[.gz]>
# ============================================================================

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR"

if [ $# -lt 1 ]; then
    echo "Usage: ./restore.sh <backup_file.sql[.gz]>"
    echo ""
    echo "Available backups:"
    ls -lh storage/backups/*.sql* 2>/dev/null || echo "  (none found)"
    exit 1
fi

BACKUP_FILE="$1"

# Read DB credentials from .env
DB_CONNECTION=$(grep -E "^DB_CONNECTION=" .env 2>/dev/null | cut -d'=' -f2 | tr -d '\r' || echo "mysql")
DB_HOST=$(grep -E "^DB_HOST=" .env 2>/dev/null | cut -d'=' -f2 | tr -d '\r' || echo "127.0.0.1")
DB_PORT=$(grep -E "^DB_PORT=" .env 2>/dev/null | cut -d'=' -f2 | tr -d '\r' || echo "3306")
DB_DATABASE=$(grep -E "^DB_DATABASE=" .env 2>/dev/null | cut -d'=' -f2 | tr -d '\r' || echo "hbci")
DB_USERNAME=$(grep -E "^DB_USERNAME=" .env 2>/dev/null | cut -d'=' -f2 | tr -d '\r' || echo "root")
DB_PASSWORD=$(grep -E "^DB_PASSWORD=" .env 2>/dev/null | cut -d'=' -f2 | tr -d '\r' || echo "")

# Handle relative paths
if [[ "$BACKUP_FILE" != /* ]]; then
    BACKUP_FILE="${SCRIPT_DIR}/${BACKUP_FILE}"
fi

if [ ! -f "$BACKUP_FILE" ]; then
    echo "ERROR: Backup file not found: $BACKUP_FILE"
    echo ""
    echo "Available backups:"
    ls -lh storage/backups/*.sql* 2>/dev/null || echo "  (none found)"
    exit 1
fi

echo "============================================"
echo "  The Hive System - Database Restore"
echo "============================================"
echo "Database:  $DB_DATABASE"
echo "Backup:    $BACKUP_FILE"
echo ""

# Confirm destructive operation
read -p "WARNING: This will REPLACE the current database. Continue? (y/N) " -n 1 -r
echo
if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    echo "Aborted."
    exit 0
fi

# Decompress if needed
SQL_FILE="$BACKUP_FILE"
if [[ "$BACKUP_FILE" == *.gz ]]; then
    echo "Decompressing..."
    SQL_FILE="${BACKUP_FILE%.gz}"
    gunzip -c "$BACKUP_FILE" > "$SQL_FILE"
fi

# MySQL connection args
MYSQL_ARGS=()
if [ -n "$DB_PASSWORD" ]; then
    MYSQL_ARGS+=(-p"${DB_PASSWORD}")
fi
MYSQL_ARGS+=(-h"$DB_HOST" -P"$DB_PORT" -u"$DB_USERNAME" "$DB_DATABASE")

# Restore
echo "Restoring database..."
mysql "${MYSQL_ARGS[@]}" < "$SQL_FILE"

# Clean up decompressed file if we created it
if [[ "$BACKUP_FILE" == *.gz ]]; then
    rm -f "$SQL_FILE"
fi

echo ""
echo "Restore complete."