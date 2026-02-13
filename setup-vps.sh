#!/bin/bash
set -e

echo "--- 📦 1. Mise à jour et Dépendances ---"
sudo apt update && sudo apt upgrade -y
sudo apt install -y software-properties-common curl git unzip ufw libreoffice openjdk-17-jre fonts-noto-core

echo "--- 🐘 2. PHP 8.4 ---"
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install -y php8.4-fpm php8.4-mysql php8.4-gd php8.4-intl php8.4-zip php8.4-bcmath php8.4-curl php8.4-xml php8.4-mbstring

echo "--- 🌐 3. Nginx ---"
sudo apt install -y nginx
# On copie la config nginx fournie vers le dossier de destination
sudo cp ./ngix_prod.conf /etc/nginx/sites-available/default
sudo systemctl restart nginx

echo "--- 📜 4. Composer & Node ---"
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs

echo "--- 📁 5. Installation des fichiers ---"
# On vide le dossier par défaut d'Nginx et on y met le projet
sudo rm -rf /var/www/html/*
sudo cp -r ./* /var/www/html/
sudo cp .env.example /var/www/html/.env
cd /var/www/html

echo "--- 📦 6. Dépendances Projet ---"
composer install --no-interaction --optimize-autoloader
npm install && npm run build
php artisan key:generate

echo "--- 🔐 7. Permissions ---"
sudo chown -R $USER:www-data /var/www/html
sudo chgrp -R www-data storage bootstrap/cache
sudo chmod -R ug+rwx storage bootstrap/cache

echo "--- 🔒 8. Firewall ---"
sudo ufw allow OpenSSH
sudo ufw allow 'Nginx Full'
sudo ufw --force enable

echo "---------------------------------------------------"
echo "✅ Installation système réussie !"
echo "---------------------------------------------------"
echo "ATTENTION : Allez dans /var/www/html pour configurer votre .env"
echo "Puis lancez : php artisan migrate --seed "
