#!/bin/bash

cd /home/smcuser/SMC-DEMO
git pull origin Sujal
sudo apt update
composer install
composer update
composer upgrade
php artisan key:generate
php artisan migrate:fresh 
php artisan db:seed
rm -rf public/storage
php artisan storage:link
php artisan serve --port=8080 &
