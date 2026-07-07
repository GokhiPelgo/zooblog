# Imagen base con PHP 8.4 (tus dependencias requieren >=8.4.1)
FROM php:8.4-cli

# Dependencias del sistema + extensiones de PHP que Laravel/Filament necesitan
RUN apt-get update && apt-get install -y \
        git unzip \
        libpq-dev libzip-dev libpng-dev libjpeg-dev libfreetype6-dev libicu-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_pgsql zip bcmath gd intl \
    && rm -rf /var/lib/apt/lists/*

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . /app

# Instala dependencias de producción
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Render inyecta el puerto en la variable $PORT
EXPOSE 8000
CMD php artisan migrate --force \
    && (php artisan db:seed --class=AdminUserSeeder --force || true) \
    && (php artisan storage:link || true) \
    && php artisan config:cache \
    && php artisan serve --host 0.0.0.0 --port ${PORT:-8000}
