##
# Imagen para demo/local: Laravel 10 + Inertia/Vite build + MySQL
# - Incluye vendor (composer) y build de Vite en /public/build
# - Expone HTTP por Apache en el puerto 80
##

FROM php:8.1-apache-bookworm AS app

# Sistema + extensiones PHP requeridas por Laravel
RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    unzip \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    default-mysql-client \
    ca-certificates \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) pdo_mysql mbstring zip gd bcmath \
    && a2enmod rewrite headers \
    && rm -rf /var/lib/apt/lists/*

# Composer (solo binario)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# DocumentRoot -> /public
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/000-default.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

WORKDIR /var/www/html

# Copiar código fuente (sin vendor/node_modules; se manejan por stages)
COPY . .

# Asegurar directorios requeridos por Laravel antes de composer (package:discover)
RUN mkdir -p bootstrap/cache \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    && chown -R www-data:www-data bootstrap/cache storage \
    && chmod -R 775 bootstrap/cache storage

# Instalar dependencias PHP (con PHP 8.1 + extensiones ya instaladas)
RUN composer install --no-dev --prefer-dist --no-interaction --no-progress --optimize-autoloader

# Nota: para Docker demo usamos el build ya presente en el repo (public/build)

# Scripts Docker
COPY docker/entrypoint.sh /usr/local/bin/entrypoint
RUN chmod +x /usr/local/bin/entrypoint \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 80

ENTRYPOINT ["entrypoint"]
CMD ["apache2-foreground"]


