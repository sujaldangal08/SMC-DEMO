#!/bin/bash

cd /home/smcuser/SMC-DEMO
git pull origin Sujal
sudo apt update
/usr/local/bin/composer install
/usr/local/bin/composer update
/usr/local/bin/composer upgrade
php artisan key:generate
php artisan migrate:fresh 
php artisan db:seed
rm -rf public/storage
php artisan storage:link
sudo php artisan serve --port=80 &
