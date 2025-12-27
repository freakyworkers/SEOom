# 마스터 운영 콘솔 가이드

## 개요

마스터 운영 콘솔은 SEOom Builder의 모든 사이트를 관리하는 중앙 관리 시스템입니다.

## 접근 방법

### URL
- 로그인: `/master/login`
- 대시보드: `/master/dashboard`

### 기본 계정
시드 실행 후:
- 이메일: `admin@seoom.com`
- 비밀번호: `admin123`

## 주요 기능

### 1. 대시보드 (`/master/dashboard`)
- 전체 시스템 통계
- 최근 생성된 사이트
- 최근 가입한 사용자
- 빠른 작업 메뉴

### 2. 사이트 관리 (`/master/sites`)
- 사이트 목록 (검색, 필터링)
- 사이트 생성 (자동 Provisioning)
- 사이트 수정
- 사이트 정지/활성화
- 사이트 삭제
- SSO 로그인 (마스터 → 고객 관리자)

### 3. 모니터링 (`/master/monitoring`)
- 전체 사이트 통계
- 데이터베이스 용량
- 사용자 수 TOP 10
- 게시글 수 TOP 10

### 4. 백업 관리 (`/master/backup`)
- 전체 백업 생성
- 사이트별 백업 생성
- 백업 다운로드
- 백업 삭제

## SSO (Single Sign-On)

마스터 콘솔에서 고객 사이트의 관리자 페이지로 직접 로그인할 수 있습니다.

1. 사이트 목록에서 원하는 사이트 선택
2. "SSO 로그인" 버튼 클릭
3. 해당 사이트의 관리자 계정으로 자동 로그인

## 보안

- 마스터 사용자는 별도의 인증 시스템 사용
- 일반 사용자와 완전히 분리된 권한 체계
- Super Admin, Admin, Operator 역할 지원

## 초기 설정

### 1. 마이그레이션 실행
```bash
php artisan migrate
```

### 2. 마스터 사용자 생성
```bash
php artisan db:seed --class=MasterUserSeeder
```

또는 수동으로:
```php
\App\Models\MasterUser::create([
    'name' => 'Super Admin',
    'email' => 'admin@seoom.com',
    'password' => Hash::make('admin123'),
    'role' => 'super_admin',
]);
```

## 역할 권한

- **Super Admin**: 모든 권한 (백업 관리 포함)
- **Admin**: 사이트 관리, 모니터링
- **Operator**: 모니터링만 (읽기 전용)

## 주의사항

1. 마스터 사용자 계정은 보안에 주의하세요
2. 사이트 삭제는 되돌릴 수 없습니다
3. 백업은 정기적으로 생성하는 것을 권장합니다










