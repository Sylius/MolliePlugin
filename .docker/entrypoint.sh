#!/bin/bash
set -e

echo "🚀 composer install..."
composer install --no-interaction

echo "🚀 start services..."
service nginx start
service php8.1-fpm start
service mariadb start

echo "🚀 Running database migrations..."
cd tests/Application
bin/console doctrine:database:create --if-not-exists
bin/console doctrine:schema:update -f
bin/console sylius:fixtures:load -n
bin/console assets:install

echo "🚀 Installing and building frontend assets..."
yarn && yarn build

echo "🚀 Set proper permissions..."
chown -R www-data:www-data /var/www

echo "✅ Initialization complete..."
tail -f /dev/null
