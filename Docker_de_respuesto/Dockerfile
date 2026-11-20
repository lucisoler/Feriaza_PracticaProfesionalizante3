# syntax=docker/dockerfile:1
# Imagen liviana: FrankenPHP (Caddy + PHP) sobre Alpine.
# No incluye Node ni npm (la cuota de Docker es chica): public/build debe existir
# antes de construir -> ejecutar `npm ci && npm run build` en la compu.

FROM dunglas/frankenphp:1-php8.4-alpine

ENV COMPOSER_ALLOW_SUPERUSER=1

# La imagen base ya trae pdo_sqlite y OPcache. No se compilan extensiones extra:
# el almacenamiento de Docker en esta máquina es chico y el build se queda sin espacio.

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --no-scripts --no-autoloader --prefer-dist \
    && composer clear-cache

COPY . .

RUN test -f public/build/manifest.json || (echo "Falta public/build: ejecutá 'npm run build' antes" && exit 1) \
    && mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs storage/app bootstrap/cache database \
    && composer dump-autoload --optimize --no-dev \
    && chmod -R ug+rwX storage bootstrap/cache database

EXPOSE 80

CMD ["frankenphp", "run", "--config", "/app/Caddyfile"]
