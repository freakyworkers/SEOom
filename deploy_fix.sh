#!/bin/bash
cd /var/www/seoom
sudo git pull origin main
sudo -u www-data php artisan optimize:clear
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache
sudo systemctl reload apache2
sudo systemctl reload php8.2-fpm
echo "배포 완료!"

