#!/usr/bin/env bash
# Bootstrap migration untuk DATABASE KOSONG.
#
# KENAPA PERLU INI:
# 4 migration bertanggal 2024_01_01_* (add_pkwt_template_to_projects,
# create_job_applications, add_pdp_fields_to_job_applications,
# add_salary_to_user_profiles) urutan namanya LEBIH AWAL daripada tabel yang
# mereka rujuk (projects, cms_careers, user_profiles) yang baru dibuat di
# migration 2025_11_*. Di database yang sudah lama jalan hal ini tidak kelihatan,
# tapi di server baru `php artisan migrate` PASTI gagal dengan:
#   SQLSTATE[HY000]: 1 no such table: projects
#   SQLSTATE[HY000]: 1005 errno 150 "Foreign key constraint is incorrectly formed"
#
# Script ini menjalankan migration satu per satu lewat --path dengan urutan yang
# benar (4 file tadi digeser ke setelah leave_types), tanpa mengubah file repo.
# Cukup dijalankan SEKALI saat setup awal. Deploy berikutnya pakai
# `php artisan migrate --force` biasa.
set -euo pipefail

CONTAINER="${CONTAINER:-jsmuguard-app}"
ANCHOR="2025_12_31_000005_add_leave_type_id_to_leave_requests_table.php"

run() { docker exec -i "$CONTAINER" sh -c "$1"; }

echo "==> Menyusun urutan migration..."
ALL=$(run "ls /var/www/database/migrations | grep -v '^2024_01_01_' | sort")
LATE=$(run "ls /var/www/database/migrations | grep '^2024_01_01_' | sort")

ORDERED=""
for f in $ALL; do
    ORDERED="$ORDERED $f"
    if [ "$f" = "$ANCHOR" ]; then
        ORDERED="$ORDERED $LATE"
    fi
done

TOTAL=$(echo $ORDERED | wc -w)
echo "==> Menjalankan $TOTAL migration berurutan..."

i=0
for f in $ORDERED; do
    i=$((i+1))
    out=$(run "cd /var/www && php artisan migrate --force --path=database/migrations/$f 2>&1") || true
    if echo "$out" | grep -q "SQLSTATE"; then
        echo "GAGAL di migration ke-$i: $f"
        echo "$out" | grep -m1 "SQLSTATE"
        exit 1
    fi
    printf "  [%2d/%2d] %s\n" "$i" "$TOTAL" "$f"
done

echo "==> Selesai. Status:"
run "cd /var/www && php artisan migrate:status | tail -5"
