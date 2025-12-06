# Composer 설치 완료! (에러 해결)

**상태:** Composer 의존성 설치 완료 (81개 패키지)

---

## ✅ 완료된 작업

- ✅ Composer 의존성 설치 완료
- ✅ 81개 패키지 설치 완료
- ⚠️ 에러 발생: `.env` 파일이 없어서 데이터베이스 접속 실패

---

## 🔧 에러 해결: .env 파일 생성 및 설정

에러는 `.env` 파일이 없어서 발생한 거예요. `.env` 파일을 만들고 설정하면 해결돼요!

### 1단계: .env 파일 생성

```bash
sudo cp .env.example .env
```

Enter 키 누르기

### 2단계: .env 파일 편집

```bash
sudo nano .env
```

Enter 키 누르면 편집기가 열려요.

---

## 📋 .env 파일 수정 내용

다음 내용들을 찾아서 수정하세요:

```env
APP_NAME="SEOom Builder"
APP_ENV=production
APP_DEBUG=false
APP_URL=http://54.180.2.108

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=seoom
DB_USERNAME=seoom_user
DB_PASSWORD=Tpdk1021!

MASTER_DOMAIN=54.180.2.108
```

**⚠️ 중요:**
- `APP_URL`과 `MASTER_DOMAIN`은 지금은 IP 주소로 설정 (나중에 도메인으로 변경 가능)
- `DB_PASSWORD`는 위에서 설정한 비밀번호 (`Tpdk1021!`)

---

## 💡 nano 편집기 사용법

1. **파일 편집**: 화살표 키로 이동해서 수정
2. **저장**: `Ctrl + O` → Enter
3. **나가기**: `Ctrl + X`

---

## 🎯 다음 단계 (.env 파일 수정 후)

### 3단계: 애플리케이션 키 생성

```bash
sudo -u www-data php artisan key:generate
```

Enter 키 누르기

### 4단계: 스토리지 링크 생성

```bash
sudo -u www-data php artisan storage:link
```

Enter 키 누르기

### 5단계: 파일 권한 설정

```bash
sudo chmod -R 775 storage bootstrap/cache
sudo chown -R www-data:www-data storage bootstrap/cache
```

Enter 키 누르기

### 6단계: 마이그레이션 실행

```bash
sudo -u www-data php artisan migrate --force
```

Enter 키 누르고 완료될 때까지 기다리세요.

### 7단계: 마스터 사용자 시더 실행

```bash
sudo -u www-data php artisan db:seed --class=MasterUserSeeder
```

Enter 키 누르기

### 8단계: 캐시 최적화

```bash
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache
```

Enter 키 누르기

---

## 📋 전체 명령어 (순서대로)

```bash
# .env 파일 생성
sudo cp .env.example .env

# .env 파일 편집
sudo nano .env

# (nano에서 수정 후 저장: Ctrl+O, Enter, Ctrl+X)

# 애플리케이션 키 생성
sudo -u www-data php artisan key:generate

# 스토리지 링크 생성
sudo -u www-data php artisan storage:link

# 파일 권한 설정
sudo chmod -R 775 storage bootstrap/cache
sudo chown -R www-data:www-data storage bootstrap/cache

# 마이그레이션 실행
sudo -u www-data php artisan migrate --force

# 마스터 사용자 시더 실행
sudo -u www-data php artisan db:seed --class=MasterUserSeeder

# 캐시 최적화
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache
```

---

**먼저 .env 파일을 생성하고 설정하세요!**

