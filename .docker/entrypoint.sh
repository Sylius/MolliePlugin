#!/bin/bash
set -e

echo "🚀 Update .env file..."
sed -i "s|^APP_ENV=.*|APP_ENV=${APP_ENV:-prod}|" /var/www/tests/Application/.env
sed -i "s|^APP_DEBUG=.*|APP_DEBUG=${APP_DEBUG:-0}|" /var/www/tests/Application/.env
echo "SYLIUS_FIXTURES_HOSTNAME=${SYLIUS_FIXTURES_HOSTNAME:-127.0.0.1}" >> /var/www/tests/Application/.env

echo "🚀 Composer install..."
composer install --no-interaction

echo "🚀 Start services..."
service nginx start
service php8.1-fpm start
service mariadb start
mailhog > /dev/null 2>&1 &

echo "🚀 Running database migrations..."
cd tests/Application
bin/console doctrine:database:create --if-not-exists
bin/console doctrine:schema:update -f
bin/console sylius:fixtures:load -n
bin/console assets:install

echo "🚀 Updating MAILER_DSN to use MailHog..."
sed -i 's|^MAILER_DSN=.*|MAILER_DSN=smtp://127.0.0.1:1025|' /var/www/tests/Application/.env
sed -i 's|^MAILER_URL=.*|MAILER_URL=smtp://127.0.0.1:1025|' /var/www/tests/Application/.env

echo "🚀 Installing and building frontend assets..."
yarn && yarn build

echo "🚀 Set proper permissions..."
chown -R www-data:www-data /var/www

echo -e "\n\e[1;32m🟢 Mollie plugin demo is ready and available at: http://127.0.0.1:8080\e[0m\n"
tail -f /dev/null
