FROM php:8.2-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# We do NOT copy files here. We mount them via docker-compose volume for development/production flexibility.
# But for production build, we might want to COPY.
# For this setup (hybrid), we will rely on volume mount in docker-compose.

# Set permissions for Laravel storage (if directory exists)
# Note: Since we mount volumes, these commands run on the container's view of the files
# It's better to handle this in a entrypoint script or manual setup.

# Change current user to www
# USER www-data 
# (Commented out USER www-data to allow root operations if needed for permission fixing during setup, 
# usually in prod we use non-root, but for this specific migration scenario, root helps fix vol permissions)

# Expose port 9000 and start php-fpm server
EXPOSE 9000
CMD ["php-fpm"]
