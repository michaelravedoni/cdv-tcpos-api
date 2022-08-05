#!/bin/sh

# Turn on maintenance mode
echo 'Command: down'
# php artisan down --render="errors::maintenance" --secret="1630542a-246b-4b66-afa1-dd72a4c43515"
php artisan down

# Pull the latest changes from the git repository
# git reset --hard
# git clean -df
# git pull origin master

# Install/update composer dependecies
echo 'Command: composer'
composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev --no-ansi
# composer install -q --no-ansi --no-interaction --no-scripts --no-progress --prefer-dist
# --no-interaction Do not ask any interactive question
# --no-dev  Disables installation of require-dev packages.
# --prefer-dist  Forces installation from package dist even for dev versions.

# Run generate key
# echo 'Command: key'
# php artisan key:generate

# Run storage link
echo 'Command: storage'
php artisan storage:link

# Run database migrations
echo 'Command: migrate'
# php artisan migrate:fresh --seed --force
# php artisan migrate --force --seed
php artisan migrate --force
# --force  Required to run when in production.

# Clear caches
echo 'Command: cache'
php artisan cache:clear

# Clear expired password reset tokens
# php artisan auth:clear-resets

# Clear and cache routes
# php artisan route:cache

# Clear and cache config
# php artisan config:cache

# Clear and cache views
# php artisan view:cache

# Install node modules
# npm install

# Build assets using Laravel Mix
# npm run production

# Turn off maintenance mode
echo 'Command: up'
php artisan up

# Start Queue
# php artisan queue:work
# pm2 start artisan --name cdv-tcpos-api-queue --interpreter php -- queue:work --daemon
# pm2 stop cdv-tcpos-api-queue
# pm2 start cdv-tcpos-api-queue
pm2 stop ecosystem.config.js
pm2 start ecosystem.config.js

# Start Cronless schedule
# php artisan schedule:run-cronless
# * * * * * cd /home/clients/3c5d3b2a90da0ecefab095faf2bc2fd1/sites/cdv/cdv-tcpos-api && php artisan schedule:run >> /dev/null 2>&1

echo '🚀 Deploy finished.'
