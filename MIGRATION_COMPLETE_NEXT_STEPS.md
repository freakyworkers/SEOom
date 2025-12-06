# 마이그레이션 완료! ✅ 다음 단계

**상태:** 모든 데이터베이스 마이그레이션 완료!

---

## ✅ 완료된 작업

- ✅ 모든 마이그레이션 실행 완료
- ✅ 데이터베이스 테이블 생성 완료
- ✅ 다음 단계 준비 완료

---

## 🎯 다음 단계: Laravel 설정 완료

### 1단계: 애플리케이션 키 생성 (이미 했으면 건너뛰기)

```bash
sudo -u www-data php artisan key:generate
```

Enter 키 누르기

**예상 결과:**
```
Application key set successfully.
```

### 2단계: 스토리지 링크 생성

```bash
sudo -u www-data php artisan storage:link
```

Enter 키 누르기

**예상 결과:**
```
The [public/storage] link has been connected to [storage/app/public].
```

### 3단계: 파일 권한 설정

```bash
sudo chmod -R 775 storage bootstrap/cache
sudo chown -R www-data:www-data storage bootstrap/cache
```

Enter 키 누르기 (각각)

### 4단계: 마스터 사용자 시더 실행

```bash
sudo -u www-data php artisan db:seed --class=MasterUserSeeder
```

Enter 키 누르기

**예상 결과:**
```
Seeding: MasterUserSeeder
Seeded:  MasterUserSeeder
```

### 5단계: 캐시 최적화

```bash
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache
```

Enter 키 누르기 (각각)

---

## 📋 전체 명령어 (순서대로)

```bash
# 애플리케이션 키 생성 (이미 했으면 건너뛰기)
sudo -u www-data php artisan key:generate

# 스토리지 링크 생성
sudo -u www-data php artisan storage:link

# 파일 권한 설정
sudo chmod -R 775 storage bootstrap/cache
sudo chown -R www-data:www-data storage bootstrap/cache

# 마스터 사용자 시더 실행
sudo -u www-data php artisan db:seed --class=MasterUserSeeder

# 캐시 최적화
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache
```

---

## 💡 팁

- 명령어는 **하나씩** 입력하세요
- 각 명령어가 완료될 때까지 기다리세요
- 에러가 나오면 알려주세요

---

## ✅ 완료 확인

모든 명령어가 성공적으로 실행되면:
- 애플리케이션 키가 생성되었어요
- 스토리지 링크가 생성되었어요
- 파일 권한이 설정되었어요
- 마스터 사용자가 생성되었어요
- 캐시가 최적화되었어요

---

## 🎯 다음 단계 (Laravel 설정 완료 후)

1. **Apache 가상 호스트 설정**
2. **도메인 연결** (Cloudflare 설정)

---

**위 명령어들을 순서대로 실행하세요!**

