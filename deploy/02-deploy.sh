#!/usr/bin/env bash
# Deploy pertama kali di VPS. Jalankan dari dalam folder repo (patrol/).
#   bash deploy/02-deploy.sh
#
# PRASYARAT:
#   - 01-provision-vps.sh sudah dijalankan
#   - A record absen.jsmu.co.id sudah mengarah ke IP VPS ini (cek: dig +short absen.jsmu.co.id)
#   - .env sudah diisi (lihat deploy/env.production.example & deploy/env.root.example)
set -euo pipefail

DOMAIN="absen.jsmu.co.id"
EMAIL="${LETSENCRYPT_EMAIL:-admin@jsmu.co.id}"
COMPOSE="docker compose --env-file .env -f deploy/docker-compose.prod.yml"

cd "$(dirname "$0")/.."

echo "==> Cek file .env"
[ -f .env ] || { echo "ERROR: .env di root repo belum ada (lihat deploy/env.root.example)"; exit 1; }
[ -f backend/api/.env ] || { echo "ERROR: backend/api/.env belum ada (lihat deploy/env.production.example)"; exit 1; }

echo "==> Cek DNS $DOMAIN"
RESOLVED=$(getent hosts "$DOMAIN" | awk '{print $1}' | head -1 || true)
MYIP=$(curl -s https://api.ipify.org || true)
echo "    $DOMAIN -> ${RESOLVED:-(belum ada)} | IP server ini -> $MYIP"
if [ "$RESOLVED" != "$MYIP" ]; then
    echo "    PERINGATAN: DNS belum mengarah ke server ini. Certbot akan gagal."
    read -p "    Lanjut tanpa SSL dulu? [y/N] " ok
    [ "$ok" = "y" ] || exit 1
fi

echo "==> Build & start container"
$COMPOSE up -d --build
sleep 20

echo "==> Install dependency PHP (tanpa dev)"
docker exec jsmuguard-app composer install --no-dev --optimize-autoloader --no-interaction

echo "==> APP_KEY"
grep -q '^APP_KEY=base64' backend/api/.env || docker exec jsmuguard-app php artisan key:generate --force

echo "==> Symlink storage (foto absensi & profil)"
docker exec jsmuguard-app php artisan storage:link || true

echo "==> Permission storage"
docker exec jsmuguard-app chown -R www-data:www-data storage bootstrap/cache
docker exec jsmuguard-app chmod -R 775 storage bootstrap/cache

echo "==> Migrasi database"
if docker exec jsmuguard-app php artisan migrate:status >/dev/null 2>&1 && \
   docker exec jsmuguard-app php artisan migrate:status | grep -q "Ran"; then
    echo "    Database sudah pernah dimigrasi -> pakai migrate biasa"
    docker exec jsmuguard-app php artisan migrate --force
else
    echo "    Database kosong -> pakai bootstrap urutan-benar"
    bash deploy/migrate-ordered.sh
    echo "==> Seed data awal (shift, project default, akun admin)"
    docker exec jsmuguard-app php artisan db:seed --force
fi

echo "==> Optimize Laravel"
docker exec jsmuguard-app php artisan config:cache
docker exec jsmuguard-app php artisan route:cache
docker exec jsmuguard-app php artisan view:cache
docker exec jsmuguard-app php artisan event:cache

echo "==> Terbitkan sertifikat SSL Let's Encrypt"
if bash deploy/03-issue-ssl.sh; then
    echo "==> HTTPS aktif: https://$DOMAIN/admin/login"
else
    echo "==> SSL belum terbit. App tetap jalan di http://$DOMAIN"
    echo "    Perbaiki DNS-nya lalu jalankan ulang: bash deploy/03-issue-ssl.sh"
fi

echo ""
echo "==> Status container:"
$COMPOSE ps
echo ""
echo "SELESAI. Login admin: https://$DOMAIN/admin/login"
echo "GANTI PASSWORD SEEDER SEGERA (itjsmu / admin / guard1)."
