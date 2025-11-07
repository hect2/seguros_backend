FROM php:8.3-fpm

# Instalar dependencias del sistema y extensiones necesarias
RUN apt-get update && apt-get install -y \
    git curl zip unzip libpng-dev libonig-dev libxml2-dev libzip-dev \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Instalar Composer globalmente
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy existing application directory contents
COPY . /var/www

# Configure git safe.directory (for the ownership issue)
RUN git config --global --add safe.directory /var/www

# Install dependencies
RUN composer install --ignore-platform-reqs

# Set permissions
RUN chown -R www-data:www-data /var/www