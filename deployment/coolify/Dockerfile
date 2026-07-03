FROM php:8.3-fpm-alpine AS base

# Runtime + build tooling
RUN apk add --no-cache \
    nginx supervisor \
    bash curl git \
    mysql-client \
    nodejs npm \
    && rm -rf /var/cache/apk/*

# PHP extensions. NOTE: on this Alpine/musl base the extension installer has no prebuilt
# binaries, so each extension compiles from source. `intl` (with ICU) is enormous and
# OOM-kills the build on small hosts — and the app does not use it (no NumberFormatter /
# IntlDateFormatter / Collator / Number:: anywhere) — so it is intentionally omitted.
# The remaining extensions compile quickly and cheaply.
COPY --from=mlocati/php-extension-installer:latest /usr/bin/install-php-extensions /usr/local/bin/
RUN install-php-extensions pdo_mysql mbstring exif pcntl bcmath gd zip opcache

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
