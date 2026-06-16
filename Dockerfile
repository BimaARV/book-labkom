# Stage 1: Build & Dependencies
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
# Ignore platform reqs to avoid conflicts during build, 
# but in production we ensure the extensions are installed.
RUN composer install \
    --ignore-platform-reqs \
    --no-interaction \
    --no-plugins \
    --no-scripts \
    --prefer-dist

COPY . .
RUN composer dump-autoload --optimize --no-dev

# Stage 2: Production Runtime
FROM php:8.5-fpm-alpine

# Setup environment variables
ENV TZ=Asia/Jakarta

# Install system dependencies & PHP extensions
RUN apk add --no-cache \
    curl \
    zip \
    unzip \
    git \
    libzip-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    oniguruma-dev \
    mariadb-client \
    tzdata \
    && cp /usr/share/zoneinfo/$TZ /etc/localtime \
    && echo $TZ > /etc/timezone \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql mbstring zip exif pcntl gd opcache bcmath

# Copy PHP config
COPY docker/php/php.ini /usr/local/etc/php/conf.d/production.ini

# Set working directory
WORKDIR /var/www/html

# Copy application files and vendor directory
COPY --from=vendor /app /var/www/html

# Copy scripts and make them executable
COPY docker/scripts/ /usr/local/bin/
RUN chmod +x /usr/local/bin/entrypoint.sh \
    && chmod +x /usr/local/bin/queue.sh \
    && chmod +x /usr/local/bin/scheduler.sh

# Set correct permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# Switch to non-root user
USER www-data

# Expose port 9000 for PHP-FPM
EXPOSE 9000

# Set entrypoint
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
