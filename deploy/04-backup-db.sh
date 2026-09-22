#!/usr/bin/env bash
# Backup database harian. Pasang di cron:
#   crontab -e
#   0 2 * * * cd /opt/patrol && bash deploy/04-backup-db.sh >> /var/log/jsmu-backup.log 2>&1
set -euo pipefail
cd "$(dirname "$0")/.."

STAMP=$(date +%Y%m%d-%H%M%S)
OUT="deploy/backups"
mkdir -p "$OUT"

source .env
docker exec jsmuguard-db sh -c "exec mysqldump -u root -p'$DB_ROOT_PASSWORD' --single-transaction --routines '$DB_DATABASE'" | gzip > "$OUT/db-$STAMP.sql.gz"

# Foto absensi/patroli/profil ikut dibackup — ini tidak ada di database
tar czf "$OUT/storage-$STAMP.tar.gz" -C backend/api/storage/app public 2>/dev/null || true

# Simpan 14 hari terakhir
find "$OUT" -name "db-*.sql.gz" -mtime +14 -delete
find "$OUT" -name "storage-*.tar.gz" -mtime +14 -delete

echo "$(date '+%F %T') backup OK: $OUT/db-$STAMP.sql.gz ($(du -h "$OUT/db-$STAMP.sql.gz" | cut -f1))"
