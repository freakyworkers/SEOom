# 🚀 서버 배포 가이드 (지금 바로!)

**로컬에서 Git 푸시 완료! 이제 서버에 배포하세요!**

## 📋 서버 정보

- **AWS EC2 퍼블릭 IPv4 주소:** `52.79.104.130`
- **키 파일 경로:** `C:\Users\kangd\Desktop\세움배포파일\seoom-key.pem`
- **마스터 사이트:** `seoomweb.com`
- **마스터 계정:** `master@seoom.com` / `Qkqh090909!`

---

## 📋 서버 배포 방법

### 방법 1: 배포 스크립트 사용 (권장! 가장 쉬움!)

1. **서버에 접속**
   ```bash
   ssh -i "C:\Users\kangd\Desktop\세움배포파일\seoom-key.pem" ubuntu@52.79.104.130
   ```

2. **배포 스크립트 실행**
   ```bash
   cd /var/www/seoom
   sudo bash deploy.sh
   ```

3. **완료!** 🎉

---

### 방법 2: 수동 배포 (스크립트가 없을 때)

1. **서버에 접속**
   ```bash
   ssh -i "C:\Users\kangd\Desktop\세움배포파일\seoom-key.pem" ubuntu@52.79.104.130
   ```

2. **프로젝트 폴더로 이동**
   ```bash
   cd /var/www/seoom
   ```

3. **Git에서 최신 변경사항 가져오기**
   ```bash
   sudo git pull origin main
   ```

4. **소유권 확인**
   ```bash
   sudo chown -R www-data:www-data /var/www/seoom
   ```

5. **캐시 클리어 (선택사항)**
   ```bash
   sudo -u www-data php artisan config:clear
   sudo -u www-data php artisan route:clear
   sudo -u www-data php artisan view:clear
   ```

6. **캐시 재생성**
   ```bash
   sudo -u www-data php artisan config:cache
   sudo -u www-data php artisan route:cache
   sudo -u www-data php artisan view:cache
   ```

7. **Apache 재시작**
   ```bash
   sudo systemctl reload apache2
   ```

8. **완료!** 🎉

---

## ✅ 배포 후 확인 사항

1. **웹사이트 접속 확인**
   - 도메인으로 접속해보세요
   - 예: `https://seoomweb.com`

2. **마스터 콘솔 로그인 확인**
   - `https://seoomweb.com/master/login` 접속
   - 이메일: `master@seoom.com`
   - 비밀번호: `Qkqh090909!`
   - 로그인 테스트

3. **이미지 미리보기 기능 확인**
   - 마스터 콘솔 → 사이트 설정
   - 로고/파비콘/OG 이미지 업로드
   - 미리보기가 표시되는지 확인

---

## 🆘 문제 해결

### 문제 1: Git pull이 안 돼요
```bash
# Git 상태 확인
cd /var/www/seoom
git status

# 충돌이 있는 경우
sudo git stash
sudo git pull origin main
```

### 문제 2: 권한 오류가 발생해요
```bash
# 소유권 재설정
sudo chown -R www-data:www-data /var/www/seoom
sudo chmod -R 755 /var/www/seoom
sudo chmod -R 775 storage bootstrap/cache
```

### 문제 3: Apache가 재시작이 안 돼요
```bash
# Apache 상태 확인
sudo systemctl status apache2

# 에러 로그 확인
sudo tail -f /var/log/apache2/error.log
```

---

## 📝 배포 완료 체크리스트

- [ ] 서버에 접속 성공
- [ ] Git pull 완료
- [ ] 소유권 설정 완료
- [ ] 캐시 재생성 완료
- [ ] Apache 재시작 완료
- [ ] 웹사이트 접속 확인
- [ ] 이미지 미리보기 기능 확인

---

**배포 완료 후 문제가 있으면 알려주세요!** 🚀

