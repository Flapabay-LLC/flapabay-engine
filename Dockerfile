# Multi-stage Dockerfile for Laravel application
FROM php:8.2-fpm-alpine AS base

# Install system dependencies
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    zip \
    unzip \
    oniguruma-dev \
    mysql-client \
    supervisor

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip

# Install Redis extension from Alpine package
RUN apk add --no-cache php82-redis

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Create application directory
WORKDIR /var/www/html

# Copy composer files
COPY composer.json composer.lock ./

# Install PHP dependencies (including dev dependencies for staging)
RUN composer install --optimize-autoloader --no-scripts --no-interaction

# Copy application code
COPY . .

# Clear bootstrap cache and regenerate autoloader
RUN rm -rf bootstrap/cache/*.php \
    && composer dump-autoload --optimize

# Set proper permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# Copy configuration files
COPY docker/php/php.ini /usr/local/etc/php/conf.d/custom.ini
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Create logs directory
RUN mkdir -p /var/log/supervisor

# Expose port
EXPOSE 9000

# Run supervisor
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]

# Production stage with Node.js for asset building
FROM node:18-alpine AS assets

WORKDIR /app

# Copy package files
COPY package.json package-lock.json ./

# Install dependencies (including devDependencies for build tools)
RUN npm ci

# Copy source files
COPY . .

# Copy vendor directory from base stage to make Ziggy available
COPY --from=base /var/www/html/vendor ./vendor

# Build assets
RUN npm run build

# Final production stage
FROM base AS production

# Copy built assets from assets stage
COPY --from=assets /app/public /var/www/html/public

# Run optimizations (skip if no .env file exists)
RUN if [ -f .env ]; then \
        php artisan config:cache && \
        php artisan route:cache && \
        php artisan view:cache; \
    fi

# Health check
HEALTHCHECK --interval=30s --timeout=10s --start-period=5s --retries=3 \
    CMD php artisan tinker --execute="echo 'OK';" || exit 1