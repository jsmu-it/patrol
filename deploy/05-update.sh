#!/usr/bin/env bash
# Deploy update (setelah ada commit baru di GitHub). Bukan untuk setup pertama.
#   bash deploy/05-update.sh
set -euo pipefail
cd "$(dirname "$0")/.."
COMPOSE="docker compose --env-file .env -f deploy/docker-compose.prod.yml"

echo "==> Backup dulu sebelum update"
bash deploy/04-backup-db.sh

echo "==> Tarik kode terbaru"
git pull --ff-only

echo "==> Maintenance mode"
docker exec jsmuguard-app php artisan down --retry=60 || true

$COMPOSE up -d --build app queue scheduler webserver
docker exec jsmuguard-app composer install --no-dev --optimize-autoloader --no-interaction
docker exec jsmuguard-app php artisan migrate --force

docker exec jsmuguard-app php artisan config:cache
docker exec jsmuguard-app php artisan route:cache
docker exec jsmuguard-app php artisan view:cache
docker exec jsmuguard-app php artisan event:cache

# Queue worker memuat kode lama di memori — wajib direstart setelah deploy
docker exec jsmuguard-app php artisan queue:restart
$COMPOSE restart queue scheduler

docker exec jsmuguard-app php artisan up
echo "==> Update selesai: https://absen.jsmu.co.id"
