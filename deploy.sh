#!/bin/bash

cd /home/smclaravel/SMC-DEMO
git pull origin Sujal
apt update
/usr/local/bin/composer install
/usr/local/bin/composer update
/usr/local/bin/composer upgrade
php artisan key:generate
php artisan migrate:fresh 
php artisan db:seed
rm -rf public/storage
php artisan storage:link
php artisan serve --port=8000 --host 0.0.0.0
