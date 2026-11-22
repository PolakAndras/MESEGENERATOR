# Éles kép a Renderhez (Tech Stack 10., NY-02 döntés: PHP 8.1 + Apache, a hivatalos php képre építve).
# A Render PHP-t csak Dockerrel futtat; a kép minden main-re érkező push után újraépül.
# Production image for Render (Tech Stack 10., NY-02 decision: PHP 8.1 + Apache on the official php image).
# Render only runs PHP via Docker; the image is rebuilt after every push to main.
FROM php:8.1-apache

# A Laravelhez és a MySQL-hez szükséges bővítmények; a gd nem kell, mert képet nem dolgozunk fel.
# Extensions needed by Laravel and MySQL; no gd, because no image processing happens.
RUN apt-get update \
    && apt-get install -y --no-install-recommends git unzip libzip-dev \
    && docker-php-ext-install pdo_mysql zip opcache \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# A dokumentumgyökér a public mappa; a Render a PORT változóban adja meg a portot (alapból 10000).
# The document root is public; Render passes the port in the PORT variable (10000 by default).
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public \
    PORT=10000
RUN sed -ri 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri 's!Listen 80!Listen ${PORT}!' /etc/apache2/ports.conf \
    && sed -ri 's!<VirtualHost \*:80>!<VirtualHost *:${PORT}>!' /etc/apache2/sites-available/000-default.conf

WORKDIR /var/www/html

# Előbb csak a függőségek, hogy a kódváltozás ne építse újra a vendor réteget.
# Dependencies first, so a code change does not rebuild the vendor layer.
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --no-interaction --prefer-dist

COPY . .
RUN composer dump-autoload --optimize --no-dev \
    && chown -R www-data:www-data storage bootstrap/cache

COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 10000
ENTRYPOINT ["entrypoint.sh"]
CMD ["apache2-foreground"]
