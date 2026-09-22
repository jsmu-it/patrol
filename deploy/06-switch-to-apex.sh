#!/usr/bin/env bash
# Pindahkan aplikasi dari absen.jsmu.co.id ke jsmu.co.id (apex).
# www.jsmu.co.id dan absen.jsmu.co.id tetap hidup, dialihkan ke apex.
#
# PRASYARAT (WAJIB dicek dulu, script ini akan menolak kalau belum):
#   - A record jsmu.co.id sudah mengarah ke IP VPS ini
#   - mail.jsmu.co.id TIDAK lagi CNAME ke apex (kalau tidak, email putus)
set -euo pipefail

APEX="jsmu.co.id"
EMAIL="${LETSENCRYPT_EMAIL:-admin@jsmu.co.id}"
COMPOSE="docker compose --env-file .env -f deploy/docker-compose.prod.yml"

cd "$(dirname "$0")/.."
MYIP=$(curl -s https://api.ipify.org)

echo "==> Cek DNS"
for h in "$APEX" "www.$APEX" "absen.$APEX"; do
    r=$(dig +short A "$h" @1.1.1.1 | tail -1)
    printf "    %-22s -> %s\n" "$h" "${r:-(kosong)}"
    if [ "$r" != "$MYIP" ]; then
        echo "    GAGAL: $h belum mengarah ke $MYIP. Perbaiki DNS dulu."
        exit 1
    fi
done

echo "==> Cek pengaman email"
MAIL_IP=$(dig +short A "mail.$APEX" @1.1.1.1 | tail -1)
echo "    mail.$APEX -> ${MAIL_IP:-(kosong)}"
if [ "$MAIL_IP" = "$MYIP" ]; then
    echo "    BERHENTI: mail.$APEX menunjuk ke VPS ini, yang tidak punya mail server."
    echo "    Ubah dulu record 'mail' di Cloudflare jadi A -> 45.130.231.112 (bukan CNAME ke apex)."
    exit 1
fi
MX=$(dig +short MX "$APEX" @1.1.1.1)
echo "    MX -> ${MX:-(KOSONG - BAHAYA)}"
[ -n "$MX" ] || { echo "    BERHENTI: MX hilang."; exit 1; }

echo "==> Pastikan nginx melayani HTTP untuk ACME"
cp deploy/nginx/apex-http.conf deploy/nginx/absen.conf
$COMPOSE restart webserver
sleep 3

echo "==> Terbitkan sertifikat untuk 3 nama sekaligus"
$COMPOSE run --rm --entrypoint certbot certbot \
    certonly --webroot -w /var/www/certbot \
    --cert-name "$APEX" \
    -d "$APEX" -d "www.$APEX" -d "absen.$APEX" \
    --email "$EMAIL" --agree-tos --no-eff-email --non-interactive

echo "==> Aktifkan konfigurasi HTTPS apex"
cp deploy/nginx/apex-ssl.conf deploy/nginx/absen.conf
$COMPOSE restart webserver
sleep 3

echo "==> Arahkan aplikasi ke apex"
sed -i -e "s|^APP_URL=.*|APP_URL=https://$APEX|" \
       -e "s|^#\?SESSION_DOMAIN=.*|SESSION_DOMAIN=$APEX|" \
       -e "s|^SANCTUM_STATEFUL_DOMAINS=.*|SANCTUM_STATEFUL_DOMAINS=$APEX,www.$APEX,absen.$APEX|" \
       backend/api/.env
docker exec jsmuguard-app php artisan config:cache
docker exec jsmuguard-app php artisan route:cache

echo "==> Verifikasi"
for u in "https://$APEX/admin/login" "https://www.$APEX/" "https://absen.$APEX/"; do
    printf "    %-40s %s\n" "$u" "$(curl -s -o /dev/null -w '%{http_code} %{redirect_url}' "$u")"
done
echo "Selesai. Aplikasi sekarang di https://$APEX"
