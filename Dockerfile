# Use the official PHP 8.2 FPM image as a base
FROM php:8.2-fpm

# Set working directory
WORKDIR /var/www/html

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
    libicu-dev \
    libpq-dev

# Install PHP extensions
RUN docker-php-ext-configure pdo_mysql --with-pdo-mysql=mysqlnd \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Install Composer
COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

# Copy the existing application directory contents to the container
COPY . .

# Copy the PHP configuration file (optional)
COPY ./php/local.ini /usr/local/etc/php/conf.d/local.ini

# Set permissions for Laravel
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Install dependencies with Composer
RUN composer update --no-ansi --no-interaction --no-progress

# Cache Laravel configuration
RUN php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache

# Copy .env.example to .env
RUN cp .env.example .env

# Generate APP_KEY
RUN php artisan key:generate

# Reset autoload
RUN composer dump-autoload

# Generate JWT key
RUN php artisan jwt:secret

# Expose port 9000 and start PHP-FPM server
EXPOSE 9000
CMD ["php-fpm"]
