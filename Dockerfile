# syntax=docker/dockerfile:1

# --- Stage 1: install production dependencies ---
FROM composer:2 AS vendor
WORKDIR /app
COPY . .
RUN composer install --no-dev --no-scripts --no-interaction --prefer-dist --optimize-autoloader

# --- Stage 2: runtime ---
FROM php:8.4-alpine
WORKDIR /app

COPY . .
COPY --from=vendor /app/vendor ./vendor

# Logger writes to logs/error.log at runtime; ensure it exists and is writable.
RUN mkdir -p logs && chown -R www-data:www-data logs

USER www-data
EXPOSE 8080

CMD ["php", "-S", "0.0.0.0:8080", "-t", "public"]
