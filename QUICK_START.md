# 빠른 시작 가이드

## 🚀 5분 안에 시작하기

### 1단계: PHP & Composer 설치

#### 옵션 A: 자동 설치 (Chocolatey 필요)
```powershell
# 관리자 권한으로 PowerShell 실행 후
.\install-php-composer.ps1
```

#### 옵션 B: 수동 설치 (권장)
1. **Laragon 다운로드**: https://laragon.org/download/
   - "Laragon Full" 다운로드
   - 설치 후 실행
   - Composer는 자동 포함

2. **또는 XAMPP + Composer**
   - XAMPP: https://www.apachefriends.org/download.html
   - Composer: https://getcomposer.org/download/

#### 설치 확인
```bash
php --version
composer --version
```

---

### 2단계: 프로젝트 설정

```bash
# 1. 의존성 설치
composer install

# 2. 환경 설정
copy .env.example .env
php artisan key:generate

# 3. .env 파일 수정 (데이터베이스 정보)
# DB_DATABASE=seoom
# DB_USERNAME=root
# DB_PASSWORD=your_password

# 4. 데이터베이스 생성
# MySQL에서: CREATE DATABASE seoom;

# 5. 마이그레이션 실행
php artisan migrate
php artisan db:seed --class=MasterUserSeeder

# 6. 개발 서버 실행
php artisan serve
```

---

### 3단계: 접속

- **마스터 콘솔**: http://localhost:8000/master/login
  - 이메일: `admin@seoom.com`
  - 비밀번호: `admin123`

- **첫 사이트 생성**: 마스터 콘솔 → 사이트 관리 → 사이트 생성

---

## 문제 해결

### PHP가 인식되지 않을 때
1. 환경 변수 Path에 PHP 경로 추가
2. 명령 프롬프트 재시작

### Composer 오류
- PHP가 먼저 설치되어 있어야 합니다
- Composer-Setup.exe를 관리자 권한으로 실행

### 데이터베이스 연결 오류
- MySQL이 실행 중인지 확인
- `.env` 파일의 DB 설정 확인

---

## 상세 가이드

- [PHP & Composer 설치](INSTALL_PHP_COMPOSER.md)
- [프로젝트 설정](SETUP_GUIDE.md)
- [다음 단계](NEXT_STEPS.md)










