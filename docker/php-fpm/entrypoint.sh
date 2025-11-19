#!/bin/bash
set -e

cd /var/www/app

if [ ! -f .env ]; then
    if [ -f .env.dev ]; then
        echo "Copying .env.dev to .env"
        cp .env.dev .env
    else
        echo "Warning: .env.dev file not found"
    fi
fi

if [ ! -d "vendor" ]; then
    echo "Installing Composer dependencies"
    composer install --no-interaction --prefer-dist
fi

if [ ! -d "node_modules" ]; then
    echo "Installing npm dependencies"
    npm install
fi

echo "Building assets"
npm run dev

echo "Waiting for MySQL to be ready"
until php bin/console doctrine:query:sql "SELECT 1" > /dev/null 2>&1; do
    echo "MySQL is unavailable - sleeping"
    sleep 2
done
echo "MySQL is ready"

echo "Migrations in progress"
set +e
php bin/console doctrine:migrations:migrate --no-interaction
MIGRATION_EXIT_CODE=$?
set -e
if [ $MIGRATION_EXIT_CODE -ne 0 ]; then
    echo "Warning: Migrations have already been applied or an error occurred (code: $MIGRATION_EXIT_CODE)"
fi

echo "Migrations for test db in progress "
set +e
APP_ENV=test php bin/console doctrine:migrations:migrate --no-interaction
MIGRATION_EXIT_CODE=$?
set -e
if [ $MIGRATION_EXIT_CODE -ne 0 ]; then
    echo "Warning: Migrations have already been applied or an error occurred (code: $MIGRATION_EXIT_CODE)"
fi

echo "Fixtures in progress"
set +e
php bin/console doctrine:fixtures:load --no-interaction
FIXTURES_EXIT_CODE=$?
set -e
if [ $FIXTURES_EXIT_CODE -ne 0 ]; then
    echo "Warning: Fixtures have already been applied or an error occurred (code: $FIXTURES_EXIT_CODE)"
fi

echo "Initialization complete"

PHP_FPM_CMD=$(which php-fpm8.4 || which php-fpm || echo "/usr/sbin/php-fpm8.4")

if [ -z "$PHP_FPM_CMD" ] || [ ! -f "$PHP_FPM_CMD" ]; then
    echo "Error: php-fpm command not found"
    exit 1
fi

echo "Starting PHP-FPM: $PHP_FPM_CMD"
exec "$PHP_FPM_CMD" --nodaemonize "$@"
