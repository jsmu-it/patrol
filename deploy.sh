#!/bin/bash

# Setup Script for Server Deployment

echo "Starting Deployment Setup..."

# 1. Check for Docker
if ! command -v docker &> /dev/null; then
    echo "Docker could not be found. Please install Docker first."
    exit 1
fi

# 2. Create .env file if not exists
if [ ! -f .env ]; then
    echo "Creating .env file..."
    cat <<EOT >> .env
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=jsmuguard_db
DB_USERNAME=jsmu_user
DB_PASSWORD=jsmu_password
EOT
fi

# 3. Build and Start Containers
echo "Building and starting containers..."
docker-compose up -d --build

# 4. Waiting for Database
echo "Waiting for Database to initialize..."
sleep 20

# 5. Setup Laravel
echo "Setting up Laravel..."
docker-compose exec -T app composer install --no-dev --optimize-autoloader
docker-compose exec -T app php artisan key:generate
docker-compose exec -T app php artisan storage:link
docker-compose exec -T app php artisan config:cache
docker-compose exec -T app php artisan route:cache
docker-compose exec -T app php artisan view:cache

# 6. Migrate Database
echo "Migrating Database..."
docker-compose exec -T app php artisan migrate --force --seed

echo "Deployment Completed! App should be running on http://<server-ip>"
