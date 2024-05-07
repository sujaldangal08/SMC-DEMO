#!/bin/bash

cd /home/smcuser/SMC-DEMO
git pull origin Sujal
sudo apt update
/usr/local/bin/composer install
/usr/local/bin/composer update
/usr/local/bin/composer upgrade
sudo php artisan key:generate
sudo php artisan migrate:fresh 
sudo php artisan db:seed
rm -rf public/storage
sudo php artisan storage:link
sudo php artisan serve --port=3000 &
