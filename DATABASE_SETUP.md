# 데이터베이스 설정 가이드

## ⚠️ 중요: MySQL 서버가 실행 중이어야 합니다

현재 MySQL 서버에 연결할 수 없습니다. 다음 단계를 따라주세요:

---

## 방법 1: Laragon에서 MySQL 시작 (권장)

1. **Laragon 실행**
2. **"모두 시작" 버튼 클릭** 또는
3. **MySQL만 시작**: MySQL 옆의 토글 스위치를 켜기

---

## 방법 2: 수동으로 MySQL 시작

### Laragon 사용 시:
- Laragon → 메뉴 → MySQL → Start

### 또는 명령어로:
```bash
# Laragon의 MySQL 경로
C:\laragon\bin\mysql\mysql-8.4.3-winx64\bin\mysqld.exe
```

---

## 데이터베이스 생성

MySQL이 실행되면 다음 명령어로 데이터베이스를 생성할 수 있습니다:

### 방법 A: Laragon 터미널 사용
1. Laragon → "터미널" 버튼 클릭
2. 다음 명령어 실행:
```bash
mysql -u root -e "CREATE DATABASE IF NOT EXISTS seoom CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

### 방법 B: Laravel 마이그레이션으로 자동 생성
`.env` 파일에서 데이터베이스 설정이 올바르면, 마이그레이션 실행 시 자동으로 생성됩니다.

---

## .env 파일 확인

`.env` 파일의 데이터베이스 설정을 확인하세요:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=seoom
DB_USERNAME=root
DB_PASSWORD=
```

**비밀번호가 설정되어 있다면 `DB_PASSWORD`에 입력하세요.**

---

## 다음 단계

MySQL이 실행되면:

```bash
# 1. 데이터베이스 연결 테스트
php artisan migrate --pretend

# 2. 마이그레이션 실행
php artisan migrate

# 3. 시더 실행
php artisan db:seed --class=MasterUserSeeder
```

---

## 문제 해결

### "연결을 거부했습니다" 오류
- ✅ Laragon에서 MySQL이 실행 중인지 확인
- ✅ 포트 3306이 사용 중인지 확인
- ✅ 방화벽 설정 확인

### "Access denied" 오류
- ✅ MySQL root 비밀번호 확인
- ✅ `.env` 파일의 `DB_PASSWORD` 설정 확인

### 데이터베이스가 생성되지 않음
- ✅ MySQL 사용자 권한 확인
- ✅ 수동으로 데이터베이스 생성 시도

---

**MySQL이 실행되면 알려주세요! 마이그레이션을 진행하겠습니다.** 🚀









