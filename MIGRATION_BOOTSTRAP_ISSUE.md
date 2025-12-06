# 마이그레이션 실행 전 부트스트랩 문제 해결

**에러:** 마이그레이션 실행 전에 `sites` 테이블을 조회하려고 해요

**원인:** Laravel 부트스트랩 과정에서 데이터베이스를 조회하고 있어요

---

## 🔧 해결 방법

### 방법 1: APP_KEY 먼저 생성 (권장)

APP_KEY가 없으면 Laravel이 제대로 작동하지 않을 수 있어요.

```bash
sudo -u www-data php artisan key:generate --force
```

Enter 키 누르기

**예상 결과:**
```
Application key set successfully.
```

그 다음 마이그레이션 실행:

```bash
sudo -u www-data php artisan migrate --force
```

---

### 방법 2: 캐시 클리어 후 시도

```bash
sudo -u www-data php artisan config:clear
sudo -u www-data php artisan cache:clear
```

Enter 키 누르기

그 다음 마이그레이션 실행:

```bash
sudo -u www-data php artisan migrate --force
```

---

### 방법 3: 직접 MySQL에서 마이그레이션 실행

만약 위 방법들이 안 되면, 직접 MySQL에서 마이그레이션 SQL을 실행할 수 있어요.

---

## 📋 추천 순서

### 1단계: APP_KEY 생성

```bash
sudo -u www-data php artisan key:generate --force
```

### 2단계: 캐시 클리어

```bash
sudo -u www-data php artisan config:clear
sudo -u www-data php artisan cache:clear
```

### 3단계: 마이그레이션 실행

```bash
sudo -u www-data php artisan migrate --force
```

---

## 💡 왜 이런 문제가 발생하나요?

- Laravel 부트스트랩 과정에서 RouteServiceProvider나 미들웨어가 데이터베이스를 조회하려고 해요
- 하지만 아직 테이블이 없어서 에러가 발생해요
- APP_KEY를 먼저 생성하면 부트스트랩이 더 안정적으로 작동할 수 있어요

---

## 🆘 여전히 안 되면

마이그레이션 파일을 직접 확인하고, 필요하면 MySQL에서 직접 실행할 수도 있어요.

---

**먼저 APP_KEY를 생성해보세요!**

