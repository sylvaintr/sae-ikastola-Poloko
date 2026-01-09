#!/bin/sh
set -e

# Attendre que MySQL soit prêt
echo "En attente de MySQL..."
while ! nc -z mysql 3306; do
  sleep 0.1
done
echo "MySQL est prêt !"

# Vérifier et installer les dépendances Composer si nécessaire
if [ ! -d "vendor" ]; then
    echo "Installation des dépendances Composer..."
    composer install --no-interaction --prefer-dist --optimize-autoloader
fi

# Vérifier et installer les dépendances npm si nécessaire
if [ -f "package.json" ] && [ ! -d "node_modules" ]; then
    echo "Installation des dépendances Node.js..."
    npm install
fi

# Créer les dossiers de stockage et configurer les permissions
mkdir -p storage/framework/views \
    storage/framework/cache \
    storage/framework/sessions \
    storage/framework/testing \
    bootstrap/cache

# Configurer les permissions
chown -R www-data:www-data storage bootstrap/cache || true
chmod -R ug+rwX storage bootstrap/cache || true

# Créer le lien symbolique pour le stockage
php artisan storage:link || true

# Exécuter la commande passée en paramètre
exec "$@"

