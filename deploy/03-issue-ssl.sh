#!/usr/bin/env bash
# Terbitkan sertifikat Let's Encrypt untuk absen.jsmu.co.id lalu aktifkan HTTPS.
# Aman dijalankan berulang (idempoten).
#   bash deploy/03-issue-ssl.sh
set -euo pipefail

DOMAIN="absen.jsmu.co.id"
EMAIL="${LETSENCRYPT_EMAIL:-admin@jsmu.co.id}"
COMPOSE="docker compose --env-file .env -f deploy/docker-compose.prod.yml"

cd "$(dirname "$0")/.."

echo "==> Pastikan nginx sedang melayani HTTP (untuk ACME challenge)"
cp deploy/nginx/absen-http.conf deploy/nginx/absen.conf
$COMPOSE up -d webserver
$COMPOSE restart webserver
sleep 3

echo "==> Minta sertifikat dari Let's Encrypt"
$COMPOSE run --rm --entrypoint certbot certbot \
    certonly --webroot -w /var/www/certbot \
    -d "$DOMAIN" \
    --email "$EMAIL" --agree-tos --no-eff-email --non-interactive

echo "==> Sertifikat terbit. Aktifkan konfigurasi HTTPS"
cp deploy/nginx/absen-ssl.conf deploy/nginx/absen.conf
$COMPOSE restart webserver
sleep 3

echo "==> Alihkan aplikasi ke HTTPS (APP_URL + secure cookie)"
cd /opt/patrol 2>/dev/null || cd "$(dirname "$0")/.."
sed -i -e "s|^APP_URL=.*|APP_URL=https://$DOMAIN|" \
       -e "s|^SESSION_SECURE_COOKIE=.*|SESSION_SECURE_COOKIE=true|" \
       -e "s|^#\?SESSION_DOMAIN=.*|SESSION_DOMAIN=$DOMAIN|" \
       backend/api/.env
docker exec jsmuguard-app php artisan config:cache
docker exec jsmuguard-app php artisan route:cache

echo "==> Verifikasi"
curl -sI "https://$DOMAIN/admin/login" | head -3 || true
echo "Selesai. Perpanjangan otomatis ditangani container jsmuguard-certbot (cek tiap 12 jam)."
