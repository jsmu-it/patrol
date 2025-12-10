#!/bin/bash
# Optimized deployment script for JSMUGuard

set -e

echo "🚀 Starting optimized deployment..."

# Stop existing containers
echo "📦 Stopping existing containers..."
docker-compose down

# Rebuild with new configs
echo "🔨 Rebuilding containers..."
docker-compose build --no-cache app

# Start all services
echo "🚀 Starting services..."
docker-compose up -d

# Wait for services to be ready
echo "⏳ Waiting for services to start..."
sleep 10

# Run Laravel optimizations inside container
echo "⚡ Running Laravel optimizations..."
docker exec jsmuguard-app php artisan config:cache
docker exec jsmuguard-app php artisan route:cache
docker exec jsmuguard-app php artisan view:cache
docker exec jsmuguard-app php artisan event:cache

# Set permissions
echo "🔐 Setting permissions..."
docker exec jsmuguard-app chmod -R 775 storage bootstrap/cache
docker exec jsmuguard-app chown -R www-data:www-data storage bootstrap/cache

# Check status
echo "✅ Deployment complete! Checking status..."
docker-compose ps

echo ""
echo "📊 Services:"
echo "  - App:      http://localhost:80"
echo "  - PMA:      http://localhost:8081"
echo ""
echo "📈 Performance optimizations applied:"
echo "  - PHP OPcache enabled with JIT"
echo "  - Redis for cache, sessions & queue"
echo "  - MySQL tuned for high concurrency"
echo "  - Nginx optimized with gzip & buffers"
echo "  - PHP-FPM with 100 max workers"
