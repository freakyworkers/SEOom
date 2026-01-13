# 🚀 서버 배포 명령어 (지금 실행하세요!)

**GitHub 푸시 완료! 이제 서버에서 다음 명령어를 실행하세요!**

## 📋 서버 정보

- **AWS EC2 퍼블릭 IPv4 주소:** `52.79.104.130`
- **키 파일 경로:** `C:\Users\kangd\Desktop\seoom bepo\seoom-key.pem`
- **마스터 사이트:** `seoomweb.com`
- **마스터 계정:** `master@seoom.com` / `Qkqh090909!`

---

## 📋 서버 배포 명령어 (순서대로 실행)

### 1단계: 서버 접속
```bash
ssh -i "C:\Users\kangd\Desktop\세움배포파일\seoom-key.pem" ubuntu@52.79.104.130
```

### 2단계: 프로젝트 폴더로 이동
```bash
cd /var/www/seoom
```

### 3단계: Git에서 최신 변경사항 가져오기
```bash
sudo git pull origin main
```

### 4단계: 마이그레이션 실행 (새로운 위젯 간격 필드 추가)
```bash
sudo -u www-data php artisan migrate --force
```

### 5단계: Composer 의존성 확인 (필요시)
```bash
sudo -u www-data composer install --no-dev --optimize-autoloader
```

### 6단계: 캐시 클리어
```bash
sudo -u www-data php artisan config:clear
sudo -u www-data php artisan route:clear
sudo -u www-data php artisan view:clear
```

### 7단계: 캐시 재생성
```bash
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache
```

### 8단계: 소유권 확인
```bash
sudo chown -R www-data:www-data /var/www/seoom
sudo chmod -R 755 /var/www/seoom
sudo chmod -R 775 storage bootstrap/cache
```

### 9단계: Apache 재시작
```bash
sudo systemctl reload apache2
```

### 10단계: 완료 확인
```bash
sudo systemctl status apache2
```

---

## 🎯 배포 스크립트 사용 (더 쉬운 방법)

배포 스크립트가 있다면:
```bash
cd /var/www/seoom
sudo bash deploy.sh
```

---

## ✅ 배포 완료 후 확인 사항

1. **웹사이트 접속 확인**
   - 도메인으로 접속: `https://seoomweb.com`

2. **마스터 콘솔 로그인 확인**
   - `https://seoomweb.com/master/login` 접속
   - 이메일: `master@seoom.com`
   - 비밀번호: `Qkqh090909!`





---

**배포 완료 후 브라우저 테스트를 진행하세요!** 🚀

