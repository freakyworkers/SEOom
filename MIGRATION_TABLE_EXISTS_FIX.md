# 마이그레이션 테이블 존재 오류 해결

**문제:** `subscriptions` 테이블이 이미 존재해요

**원인:** 이전 마이그레이션 실행 중 일부 테이블이 생성되었어요

---

## 🔧 해결 방법: subscriptions 테이블 삭제 후 재실행

### 방법 1: MySQL에서 직접 삭제 (권장)

```bash
sudo mysql -u root
```

Enter 키 누르기

MySQL 콘솔에서:

```sql
USE seoom;
DROP TABLE IF EXISTS subscriptions;
EXIT;
```

Enter 키 누르기

그 다음 마이그레이션 다시 실행:

```bash
sudo -u www-data php artisan migrate --force
```

---

### 방법 2: 마이그레이션 롤백 후 재실행

```bash
sudo -u www-data php artisan migrate:rollback --step=1
sudo -u www-data php artisan migrate --force
```

하지만 이건 다른 테이블도 롤백될 수 있어요.

---

## 📋 추천 방법

**방법 1**이 가장 안전해요:
1. MySQL에서 `subscriptions` 테이블만 삭제
2. 마이그레이션 다시 실행

---

## 🎯 전체 순서

### 1단계: MySQL 접속

```bash
sudo mysql -u root
```

### 2단계: subscriptions 테이블 삭제

```sql
USE seoom;
DROP TABLE IF EXISTS subscriptions;
EXIT;
```

### 3단계: 마이그레이션 다시 실행

```bash
sudo -u www-data php artisan migrate --force
```

---

**MySQL에서 subscriptions 테이블을 삭제하세요!**

