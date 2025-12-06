# 마이그레이션 먼저 실행

**에러:** `Table 'seoom.sites' doesn't exist`

**원인:** 아직 마이그레이션을 실행하지 않아서 테이블이 없어요

---

## 🔧 해결 방법: 마이그레이션 먼저 실행

데이터베이스 테이블을 먼저 만들어야 해요!

### 1단계: 마이그레이션 실행

```bash
sudo -u www-data php artisan migrate --force
```

Enter 키 누르고 완료될 때까지 기다리세요.  
**예상 시간:** 1-2분

**예상 결과:**
```
Migrating: 2024_01_01_000001_create_sites_table
Migrated:  2024_01_01_000001_create_sites_table
Migrating: 2024_01_01_000002_create_users_table
Migrated:  2024_01_01_000002_create_users_table
...
```

---

## 📋 전체 순서 (수정됨)

### 1단계: 마이그레이션 실행

```bash
sudo -u www-data php artisan migrate --force
```

### 2단계: 애플리케이션 키 생성

```bash
sudo -u www-data php artisan key:generate
```

### 3단계: 스토리지 링크 생성

```bash
sudo -u www-data php artisan storage:link
```

### 4단계: 파일 권한 설정

```bash
sudo chmod -R 775 storage bootstrap/cache
sudo chown -R www-data:www-data storage bootstrap/cache
```

### 5단계: 마스터 사용자 시더 실행

```bash
sudo -u www-data php artisan db:seed --class=MasterUserSeeder
```

### 6단계: 캐시 최적화

```bash
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache
```

---

## 💡 왜 마이그레이션을 먼저?

- Laravel이 데이터베이스 테이블을 조회하려고 해요
- 테이블이 없으면 에러가 발생해요
- 마이그레이션을 먼저 실행하면 테이블이 생성돼요

---

## ✅ 완료 확인

마이그레이션이 성공하면:
- 모든 테이블이 생성되었어요
- 그 다음 명령어들이 정상적으로 실행될 거예요

---

**마이그레이션을 먼저 실행하세요!**

