# SEOom Builder

멀티테넌트 커뮤니티·홈페이지·쇼핑몰 SaaS 플랫폼

## 설치 방법

### 1. 의존성 설치
```bash
composer install
```

### 2. 환경 설정
```bash
cp .env.example .env
php artisan key:generate
```

### 3. .env 파일 수정
데이터베이스 연결 정보를 설정하세요:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=seoom
DB_USERNAME=root
DB_PASSWORD=
```

### 4. 데이터베이스 마이그레이션
```bash
php artisan migrate
php artisan db:seed --class=MasterUserSeeder
```

### 5. 개발 서버 실행
```bash
php artisan serve
```

## 접근 URL

- 홈: `http://localhost:8000`
- 마스터 로그인: `http://localhost:8000/master/login`
  - 이메일: `admin@seoom.com`
  - 비밀번호: `admin123`

## 주요 기능

- 멀티테넌트 사이트 관리
- 게시판/게시글/댓글 시스템
- 사이트별 관리자 페이지
- 마스터 운영 콘솔
- SSO (Single Sign-On)

## 문서

- [전체 README](SEOom_readme.md)
- [디자인 시스템](DESIGN_SYSTEM.md)
- [마스터 콘솔 가이드](MASTER_CONSOLE_README.md)
- [다음 단계](NEXT_STEPS.md)








