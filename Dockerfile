# ─────────────────────────────────────────────────────────────
# HALIS POS — Dockerfile for Render deployment
# Multi-stage: Node (build assets) → PHP (runtime)
# ─────────────────────────────────────────────────────────────

# ── Stage 1: Build frontend assets ──────────────────────────
FROM node:20-alpine AS assets

WORKDIR /build

COPY package.json package-lock.json ./
RUN npm ci

COPY vite.config.js ./
COPY resources/ resources/

RUN npm run build

# ── Stage 2: PHP runtime ────────────────────────────────────
FROM php:8.2-cli

# Install system dependencies + PHP extensions
RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    unzip \
    curl \
    libpq-dev \
    libzip-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libicu-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        pdo_pgsql \
        pgsql \
        gd \
        intl \
        zip \
        bcmath \
        pcntl \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy composer files first for better layer caching
COPY composer.json composer.lock ./

# Install PHP dependencies (without scripts since artisan is not copied yet)
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --no-scripts \
    --no-autoloader \
    --prefer-dist

# Copy the rest of the application
COPY . .

# Copy built frontend assets from Stage 1
COPY --from=assets /build/public/build public/build

# Create minimal .env for build-time artisan commands (package:discover)
RUN echo "APP_KEY=base64:dGVzdGtleWZvcmJ1aWxkdGltZQ==" > .env && \
    echo "DB_CONNECTION=pgsql" >> .env && \
    echo "CACHE_STORE=array" >> .env && \
    echo "SESSION_DRIVER=array" >> .env

# Generate optimized autoloader and run package discovery
RUN composer dump-autoload --optimize --no-dev

# Remove the build-time .env (Render will provide the real one)
RUN rm -f .env

# Create required Laravel directories and set permissions
RUN mkdir -p \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

# Expose the port Render will use (Render sets $PORT, default 10000)
EXPOSE 10000

# Start command: cache config/routes, run migrations, create storage link, then serve
# Use ; (not &&) so server starts even if cache/migrate fails
CMD php artisan config:cache 2>&1; \
    php artisan route:cache 2>&1; \
    php artisan view:cache 2>&1; \
    php artisan migrate --force 2>&1; \
    php artisan storage:link --force 2>/dev/null; \
    exec php artisan serve --host=0.0.0.0 --port=${PORT:-10000}
