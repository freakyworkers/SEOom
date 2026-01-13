# 🚀 지금 배포하세요!

## 서버에서 실행할 명령어 (복사해서 붙여넣기)

```bash
cd /var/www/seoom
sudo git pull origin main
sudo -u www-data php artisan config:clear
sudo -u www-data php artisan route:clear
sudo -u www-data php artisan view:clear
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache
sudo systemctl reload apache2
```

## 배포 후 테스트

1. https://web.seoomweb.com/admin/boards 접속
2. 로그인: admin1 / Qkqh090909!
3. 확인 사항:
   - admin1 사용자가 속한 사이트의 게시판만 표시되는지 확인
   - "Portfolio" 게시판이 보이는지 확인
   - 다른 사이트의 게시판이 보이지 않는지 확인
