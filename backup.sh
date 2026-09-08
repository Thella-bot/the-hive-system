#!/usr/bin/env bash
# ============================================================================
# The Hive System - Database Backup Script
# Usage: ./backup.sh [options]
# Options:
#   --daily       Daily backup (keeps last 7 days)
#   --weekly      Weekly backup (keeps last 4 weeks)
#   --monthly     Monthly backup (keeps last 12 months)
#   --manual      Single backup, no rotation
#   --compress    Compress backup with gzip
#   --help        Show this help
# ============================================================================

set -euo pipefail

# Load environment variables
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR"

# Read DB credentials from .env
DB_CONNECTION=$(grep -E "^DB_CONNECTION=" .env 2>/dev/null | cut -d'=' -f2 | tr -d '\r' || echo "mysql")
DB_HOST=$(grep -E "^DB_HOST=" .env 2>/dev/null | cut -d'=' -f2 | tr -d '\r' || echo "127.0.0.1")
DB_PORT=$(grep -E "^DB_PORT=" .env 2>/dev/null | cut -d'=' -f2 | tr -d '\r' || echo "3306")
DB_DATABASE=$(grep -E "^DB_DATABASE=" .env 2>/dev/null | cut -d'=' -f2 | tr -d '\r' || echo "hbci")
DB_USERNAME=$(grep -E "^DB_USERNAME=" .env 2>/dev/null | cut -d'=' -f2 | tr -d '\r' || echo "root")
DB_PASSWORD=$(grep -E "^DB_PASSWORD=" .env 2>/dev/null | cut -d'=' -f2 | tr -d '\r' || echo "")

BACKUP_DIR="${SCRIPT_DIR}/storage/backups"
mkdir -p "$BACKUP_DIR"

TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
BACKUP_FILE="${BACKUP_DIR}/${DB_DATABASE}_${TIMESTAMP}.sql"
COMPRESSED_FILE="${BACKUP_FILE}.gz"

# MySQL connection args
MYSQL_ARGS=()
if [ -n "$DB_PASSWORD" ]; then
    MYSQL_ARGS+=(-p"${DB_PASSWORD}")
fi
MYSQL_ARGS+=(-h"$DB_HOST" -P"$DB_PORT" -u"$DB_USERNAME" "$DB_DATABASE")

MODE="${1:---manual}"
COMPRESS=false

case "$MODE" in
    --daily|--weekly|--monthly|--manual)
        ;;
    --compress)
        MODE="--manual"
        COMPRESS=true
        ;;
    -c|--compress)
        MODE="--manual"
        COMPRESS=true
        ;;
    --help|-h)
        echo "Usage: ./backup.sh [OPTION]"
        echo ""
        echo "Options:"
        echo "  --daily      Daily backup (keeps last 7 days)"
        echo "  --weekly     Weekly backup (keeps last 4 weeks)"
        echo "  --monthly    Monthly backup (keeps last 12 months)"
        echo "  --manual     Single backup, no rotation (default)"
        echo "  --compress   Compress backup with gzip"
        echo "  --help       Show this help"
        exit 0
        ;;
esac

# Check if second arg is --compress
if [ "${2:-}" = "--compress" ] || [ "${2:-}" = "-c" ]; then
    COMPRESS=true
fi

echo "============================================"
echo "  The Hive System - Database Backup"
echo "============================================"
echo "Database:  $DB_DATABASE"
echo "Host:      $DB_HOST:$DB_PORT"
echo "Timestamp: $TIMESTAMP"
echo "Mode:      $MODE"
echo ""

# Create the backup
echo "Dumping database..."
mysqldump "${MYSQL_ARGS[@]}" \
    --single-transaction \
    --routines \
    --events \
    --triggers \
    --databases "$DB_DATABASE" > "$BACKUP_FILE"

BACKUP_SIZE=$(du -h "$BACKUP_FILE" | cut -f1)
echo "Backup created: $BACKUP_FILE ($BACKUP_SIZE)"

# Compress if requested
if [ "$COMPRESS" = true ]; then
    echo "Compressing..."
    gzip -f "$BACKUP_FILE"
    BACKUP_SIZE=$(du -h "$COMPRESSED_FILE" | cut -f1)
    echo "Compressed: $COMPRESSED_FILE ($BACKUP_SIZE)"
    BACKUP_FILE="$COMPRESSED_FILE"
fi

# Rotate old backups based on mode
echo ""
echo "Rotating old backups..."
case "$MODE" in
    --daily)
        find "$BACKUP_DIR" -name "${DB_DATABASE}_*.sql*" -type f -mtime +7 -delete 2>/dev/null || true
        echo "Kept last 7 daily backups."
        ;;
    --weekly)
        find "$BACKUP_DIR" -name "${DB_DATABASE}_*.sql*" -type f -mtime +28 -delete 2>/dev/null || true
        echo "Kept last 4 weekly backups."
        ;;
    --monthly)
        find "$BACKUP_DIR" -name "${DB_DATABASE}_*.sql*" -type f -mtime +365 -delete 2>/dev/null || true
        echo "Kept last 12 monthly backups."
        ;;
    --manual)
        echo "No rotation for manual backup."
        ;;
esac

# List current backups
echo ""
echo "Current backups in $BACKUP_DIR:"
ls -lh "$BACKUP_DIR"/${DB_DATABASE}_*.sql* 2>/dev/null || echo "  (none found)"

echo ""
echo "Backup complete."