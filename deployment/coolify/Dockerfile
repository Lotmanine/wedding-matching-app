FROM php:8.3-fpm-alpine AS base

# Runtime + build tooling
RUN apk add --no-cache \
    nginx supervisor \
    bash curl git \
    mysql-client \
    nodejs npm \
    && rm -rf /var/cache/apk/*

# PHP extensions via PREBUILT binaries (no source compilation). Compiling intl/gd/opcache
# from source is slow and memory-hungry, which OOM-kills the build on small hosts. The
# extension installer downloads ready-made binaries instead — fast and low-memory.
COPY --from=mlocati/php-extension-installer:latest /usr/bin/install-php-extensions /usr/local/bin/
RUN install-php-extensions pdo_mysql mbstring exif pcntl bcmath gd zip intl opcache

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction

COPY . .
RUN composer dump-autoload --optimize --no-dev --classmap-authoritative

RUN npm ci && npm run build && rm -rf node_modules

RUN mkdir -p storage/framework/{sessions,views,cache,testing} storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

COPY deployment/coolify/nginx.conf /etc/nginx/http.d/default.conf
COPY deployment/coolify/supervisord.conf /etc/supervisord.conf
COPY deployment/coolify/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 80

HEALTHCHECK --interval=30s --timeout=10s --retries=3 --start-period=60s \
    CMD curl -fsS http://127.0.0.1/up || exit 1

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["supervisord", "-c", "/etc/supervisord.conf"]
