# --- Étape 1 : image PHP-FPM 8.4 ---
FROM php:8.4-fpm

# Installation des dépendances système
RUN apt-get update && apt-get install -y \
    git curl zip unzip libpng-dev libonig-dev libxml2-dev \
    libzip-dev libpq-dev libicu-dev libjpeg-dev libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql pdo_pgsql mbstring exif pcntl gd intl zip bcmath

RUN pecl install xdebug \
    && docker-php-ext-enable xdebug
# Installation de Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Configuration PHP (facultatif)
COPY ./docker/php.ini /usr/local/etc/php/conf.d/custom.ini

# Définition du dossier de travail
WORKDIR /var/www/html

# Installation de Node.js et de npm
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && npm install -g npm

# Copie des fichiers du projet
COPY . .

RUN php artisan storage:link || true


RUN git config --global --add safe.directory /var/www/html

# Installation dépendances Laravel
RUN composer install --no-interaction --prefer-dist --optimize-autoloader --no-scripts

# Droits pour Laravel
RUN mkdir -p storage/logs \
    && mkdir -p storage/framework/cache \
    && mkdir -p storage/framework/sessions \
    && mkdir -p storage/framework/views \
    && mkdir -p bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R ug+rwX storage bootstrap/cache

# Port du serveur PHP-FPM
EXPOSE 9000

CMD ["php-fpm"]
