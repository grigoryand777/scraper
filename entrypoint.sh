#!/bin/bash
echo "=== API INIT SCRIPT STARTED ==="
cd api
chown -R $USER:www-data storage
chown -R $USER:www-data bootstrap/cache
chmod -R 775 storage
chmod -R 775 bootstrap/cache
php artisan storage:link
php artisan vendor:publish --all
php artisan optimize:clear
composer dump-autoload

php-fpm -D
nginx -g 'daemon off;'
echo "=== API INIT SCRIPT ENDED ==="