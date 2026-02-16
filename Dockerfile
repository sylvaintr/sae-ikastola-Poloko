    # --- Image PHP-FPM 8.4 sur Alpine ---
    FROM php:8.4-fpm-alpine

    # Récupération de l'installateur d'extensions 
    COPY --from=mlocati/php-extension-installer:2 /usr/bin/install-php-extensions /usr/local/bin/

    # 1. Installation des dépendances système 
    RUN apk add --no-cache \
        nodejs \
        npm \
        bash \
        git \
        netcat-openbsd \
        libreoffice \
        openjdk17-jre \
        ttf-freefont \
        font-noto \
        && install-php-extensions \
        pdo_mysql \
        pdo_pgsql \
        exif \
        pcntl \
        gd \
        intl \
        zip \
        bcmath \
        xdebug

    # Installation de Composer depuis l'image officielle
    COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

    # Configuration PHP personnalisée (facultatif)
    COPY ./docker/php.ini /usr/local/etc/php/conf.d/custom.ini

    # Définition du dossier de travail
    WORKDIR /var/www/html
    ENV HOME=/tmp

    # --- Étape de build (Optimisation du cache) ---

    # 3. Copie et installation des dépendances PHP
    COPY composer.json composer.lock ./
    RUN composer install --no-interaction --prefer-dist --optimize-autoloader --no-scripts

    # 4. Copie et installation des dépendances Node.js
    COPY package.json package-lock.json* ./
    RUN if [ -f package.json ]; then npm ci; fi

    # 5. Copie du reste du code source
    COPY --chown=www-data:www-data . .

    # 6. Permissions et structure des dossiers Laravel
    RUN chmod 777 /tmp \
    && mkdir -p storage/logs storage/framework/cache storage/framework/sessions storage/framework/views bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

    # 7. Lien symbolique et scripts finaux
    RUN php artisan storage:link || true

    EXPOSE 9000

    CMD ["php-fpm"]