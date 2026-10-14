FROM php:8.4-fpm-alpine

# Set working directory
WORKDIR /var/www

# Install system dependencies & libraries needed for PHP extensions
RUN apk add --no-cache \
    bash \
    curl \
    git \
    unzip \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    oniguruma-dev \
    icu-dev \
    mysql-client \
    linux-headers

# Configure and install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_mysql \
        mbstring \
        zip \
        exif \
        pcntl \
        bcmath \
        gd \
        intl \
        opcache

# Install latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy composer definition files first for optimal caching
COPY composer.json composer.lock* ./

# Install Composer dependencies (production dependencies, full autoload will dump after copy)
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist || true

# Copy application source code
COPY . /var/www

# Run composer dump-autoload
RUN composer dump-autoload --optimize || true

# Copy entrypoint script and ensure it is executable
COPY docker/entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Configure correct permissions for storage and bootstrap/cache
RUN mkdir -p storage/framework/cache/data \
             storage/framework/sessions \
             storage/framework/views \
             storage/logs \
             bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Expose PHP-FPM FastCGI port
EXPOSE 9000

# Set entrypoint
ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]

# Start PHP-FPM
CMD ["php-fpm"]
