#!/usr/bin/env bash
# Jalankan SEKALI di VPS baru (Ubuntu 22.04/24.04), sebagai root.
#   bash 01-provision-vps.sh
set -euo pipefail

echo "==> Update sistem"
export DEBIAN_FRONTEND=noninteractive
apt-get update -qq && apt-get upgrade -y -qq

echo "==> Install Docker + compose plugin"
if ! command -v docker >/dev/null 2>&1; then
    curl -fsSL https://get.docker.com | sh
fi
docker --version
docker compose version

echo "==> Install utilitas"
apt-get install -y -qq git ufw fail2ban curl

echo "==> Firewall (SSH, HTTP, HTTPS saja)"
ufw allow OpenSSH
ufw allow 80/tcp
ufw allow 443/tcp
ufw --force enable
ufw status

echo "==> Swap 2G (jaga-jaga kalau RAM VPS kecil saat build)"
if [ ! -f /swapfile ]; then
    fallocate -l 2G /swapfile
    chmod 600 /swapfile
    mkswap /swapfile
    swapon /swapfile
    echo '/swapfile none swap sw 0 0' >> /etc/fstab
fi
free -h

echo "==> Timezone Asia/Jakarta (penting untuk jam absensi & shift)"
timedatectl set-timezone Asia/Jakarta
date

echo "==> Selesai. Lanjut ke 02-deploy.sh"
