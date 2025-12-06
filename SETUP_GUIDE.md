# SEOom Builder 설치 가이드

## 1단계: Composer 의존성 설치

```bash
composer install
```

이 명령어는 `composer.json`에 정의된 모든 Laravel 패키지를 설치합니다.

---

## 2단계: 환경 설정

### 2-1. .env 파일 생성
```bash
# Windows
copy .env.example .env

# Linux/Mac
cp .env.example .env
```

### 2-2. 애플리케이션 키 생성
```bash
php artisan key:generate
```

### 2-3. .env 파일 수정
`.env` 파일을 열어서 다음 항목들을 수정하세요:

```env
APP_NAME="SEOom Builder"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=seoom
DB_USERNAME=root
DB_PASSWORD=your_password

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
```

---

## 3단계: 데이터베이스 설정

### 3-1. MySQL 데이터베이스 생성
```sql
CREATE DATABASE seoom CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 3-2. 마이그레이션 실행
```bash
php artisan migrate
```

이 명령어는 다음 테이블들을 생성합니다:
- sessions
- cache
- jobs
- password_reset_tokens
- sites
- users
- boards
- posts
- comments
- site_settings
- master_users

### 3-3. 마스터 사용자 생성
```bash
php artisan db:seed --class=MasterUserSeeder
```

기본 마스터 계정:
- 이메일: `admin@seoom.com`
- 비밀번호: `admin123`

**⚠️ 중요: 프로덕션 환경에서는 반드시 비밀번호를 변경하세요!**

---

## 4단계: 개발 서버 실행

```bash
php artisan serve
```

서버가 실행되면 브라우저에서 접속:
- `http://localhost:8000`

---

## 5단계: 첫 사이트 생성

### 방법 1: 마스터 콘솔에서 생성
1. `/master/login` 접속
2. 마스터 계정으로 로그인
3. "사이트 관리" → "사이트 생성"
4. 사이트 정보 입력 및 관리자 계정 생성

### 방법 2: 직접 데이터베이스에 삽입
```php
php artisan tinker

$site = \App\Models\Site::create([
    'name' => 'My First Site',
    'slug' => 'my-site',
    'plan' => 'free',
    'status' => 'active',
]);

$provisionService = new \App\Services\SiteProvisionService();
$provisionService->provision([
    'name' => 'My First Site',
    'slug' => 'my-site',
    'admin_email' => 'admin@example.com',
    'admin_password' => 'password123',
    'admin_name' => 'Admin',
]);
```

---

## 6단계: 접속 확인

### 마스터 콘솔
- URL: `http://localhost:8000/master/login`
- 계정: `admin@seoom.com` / `admin123`

### 사이트 접속
- URL: `http://localhost:8000/site/{slug}/`
- 예: `http://localhost:8000/site/my-site/`

---

## 문제 해결

### Composer 설치 오류
```bash
# Composer가 설치되어 있지 않다면
# https://getcomposer.org/download/ 에서 설치
```

### 데이터베이스 연결 오류
- MySQL이 실행 중인지 확인
- `.env` 파일의 DB 설정 확인
- 데이터베이스가 생성되었는지 확인

### 권한 오류 (Linux/Mac)
```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### 마이그레이션 오류
```bash
# 마이그레이션 초기화 후 재실행
php artisan migrate:fresh
php artisan db:seed --class=MasterUserSeeder
```

---

## 다음 단계

설치가 완료되면:
1. 기능 테스트 진행
2. 버그 수정
3. AWS EC2 배포 준비

자세한 내용은 [NEXT_STEPS.md](NEXT_STEPS.md)를 참고하세요.








