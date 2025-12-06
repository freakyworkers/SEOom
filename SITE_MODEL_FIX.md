# Site 모델 수정 완료

**문제:** `Site::getMasterSite()`가 테이블이 없을 때 에러 발생

**해결:** 테이블 존재 여부를 확인하도록 수정했어요

---

## ✅ 수정 완료

`app/Models/Site.php` 파일의 `getMasterSite()` 메서드를 수정했어요:
- 테이블이 없으면 `null` 반환
- 예외 발생 시에도 `null` 반환

---

## 🎯 이제 다시 시도

### 1단계: 마이그레이션 실행

```bash
sudo -u www-data php artisan migrate --force
```

Enter 키 누르고 완료될 때까지 기다리세요.

**예상 결과:**
```
Migrating: 2024_01_01_000001_create_sites_table
Migrated:  2024_01_01_000001_create_sites_table
...
```

---

## 📋 다음 단계 (마이그레이션 완료 후)

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

**마이그레이션을 다시 실행해보세요!**

