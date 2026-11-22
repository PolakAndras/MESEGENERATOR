#!/bin/sh
# Indításkor: gyorsítótárak, migráció, az admin fiók (US-19, nem duplikál) és a katalógus csak üres adatbázisba
# (catalog:install), hogy az újraindulás ne írja felül az admin módosításait. Utána az Apache.
# On start: caches, migrations, the admin account (US-19, never duplicates) and the catalog only into an empty
# database (catalog:install), so a restart never overwrites the admin's changes. Then Apache.
set -e

php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan migrate --force
php artisan db:seed --class="Database\\Seeders\\AdminUserSeeder" --force
php artisan catalog:install

exec "$@"
