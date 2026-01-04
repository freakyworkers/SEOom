# 배포 스크립트
$keyPath = "C:\Users\kangd\Desktop\세움배포파일\seoom-key.pem"
$server = "ubuntu@52.79.104.130"
$commands = @"
cd /var/www/seoom && sudo git pull origin main && sudo -u www-data php artisan config:clear && sudo -u www-data php artisan route:clear && sudo -u www-data php artisan view:clear && sudo -u www-data php artisan config:cache && sudo -u www-data php artisan route:cache && sudo -u www-data php artisan view:cache && sudo systemctl reload apache2
"@

ssh -i $keyPath $server $commands



