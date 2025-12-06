# 마이그레이션 직접 실행 방법

**문제:** Laravel 부트스트랩 과정에서 `sites` 테이블을 조회하려고 해서 마이그레이션 실행이 안 돼요

**해결:** 마이그레이션을 직접 MySQL에서 실행하거나, 부트스트랩을 우회해서 실행해요

---

## 🔧 해결 방법 1: 직접 MySQL에서 실행 (가장 확실)

### 1단계: MySQL 접속

```bash
sudo mysql -u root
```

Enter 키 누르기

### 2단계: 데이터베이스 선택

```sql
USE seoom;
```

Enter 키 누르기

### 3단계: sites 테이블 생성

```sql
CREATE TABLE sites (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    domain VARCHAR(255) NULL,
    plan VARCHAR(255) DEFAULT 'free',
    status ENUM('active', 'suspended', 'deleted') DEFAULT 'active',
    is_master_site BOOLEAN DEFAULT FALSE,
    created_by BIGINT UNSIGNED NULL,
    storage_used_mb INT DEFAULT 0,
    storage_limit_mb INT NULL,
    traffic_used_mb INT DEFAULT 0,
    traffic_limit_mb INT NULL,
    traffic_reset_date DATETIME NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    INDEX idx_slug (slug),
    INDEX idx_domain (domain),
    INDEX idx_status (status),
    INDEX idx_is_master_site (is_master_site)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

Enter 키 누르기

### 4단계: MySQL 나가기

```sql
EXIT;
```

Enter 키 누르기

### 5단계: 나머지 마이그레이션 실행

```bash
sudo -u www-data php artisan migrate --force
```

---

## 🔧 해결 방법 2: 부트스트랩 우회 (더 간단)

환경 변수를 설정해서 부트스트랩을 우회해요:

```bash
APP_ENV=local sudo -u www-data php artisan migrate --force
```

또는:

```bash
DB_CONNECTION=mysql DB_DATABASE=seoom DB_USERNAME=seoom_user DB_PASSWORD=Tpdk1021! sudo -u www-data php artisan migrate --force
```

---

## 🔧 해결 방법 3: getMasterSite() 수정 (권장)

`Site::getMasterSite()` 메서드가 테이블이 없을 때 예외를 처리하도록 수정해요.

하지만 지금은 빠르게 해결하기 위해 방법 1이나 2를 사용하는 게 좋아요.

---

## 💡 추천 방법

**방법 1 (직접 MySQL 실행)**이 가장 확실해요:
- sites 테이블만 먼저 만들고
- 나머지는 `php artisan migrate`로 실행

---

**방법 1로 진행하시겠어요, 아니면 방법 2로 시도해보시겠어요?**

