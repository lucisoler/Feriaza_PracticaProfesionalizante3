# syntax=docker/dockerfile:1
# Imagen liviana: FrankenPHP (Caddy + PHP) sobre Alpine.
# No incluye Node ni npm. public/build debe existir antes de construir (npm run build en tu compu).

FROM dunglas/frankenphp:1-php8.4-alpine

ENV COMPOSER_ALLOW_SUPERUSER=1

RUN install-php-extensions pdo_sqlite bcmath opcache intl zip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . .

RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist \
    && composer clear-cache \
    && mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs storage/app bootstrap/cache database \
    && chmod -R ug+rwX storage bootstrap/cache database

EXPOSE 80

CMD ["frankenphp", "run", "--config", "/app/Caddyfile"]
