@echo off
cd /d C:\Users\kangd\Desktop\01.seoom\SEOom
git add resources/views/admin/banners.blade.php
git commit -m "Fix: 배너 설정 저장 - hidden input 완전 제외하여 중복 값 방지"
git push origin main
ssh -i "C:\Users\kangd\Desktop\세움배포파일\seoom-key.pem" ubuntu@52.79.104.130 "cd /var/www/seoom; sudo git pull origin main; sudo -u www-data php artisan config:clear; sudo -u www-data php artisan view:clear; sudo systemctl reload apache2"
pause

