# 브라우저 테스트 에러 해결 가이드

## 🔴 발견된 에러

**에러 메시지:**
```
Missing required parameter for [Route: home] [URI: site/{site}] [Missing parameter: site]
```

**발생 위치:**
- `/var/www/seoom/resources/views/components/header-theme.blade.php`

**원인:**
1. 마스터 사이트가 데이터베이스에 없어요
2. `ResolveSiteByDomain` 미들웨어가 `seoomweb.com`을 마스터 도메인으로 인식하지만, 마스터 사이트가 없어서 `$site`가 null이에요
3. 뷰 파일에서 `route('home', ['site' => $site->slug])`를 호출할 때 `$site`가 null이라서 에러 발생

---

## ✅ 해결 방법

### 1단계: 마스터 사이트 생성 (필수)

서버에서 MySQL로 접속해서 마스터 사이트를 생성하세요:

```bash
sudo mysql -u root
```

MySQL에서:

```sql
USE seoom;

INSERT INTO sites (name, slug, domain, plan, status, is_master_site, created_at, updated_at)
VALUES ('SEOom Builder', 'master', 'seoomweb.com', 'premium', 'active', 1, NOW(), NOW());

EXIT;
```

### 2단계: .env 파일 확인

`.env` 파일에서 `MASTER_DOMAIN`이 올바르게 설정되어 있는지 확인:

```bash
sudo cat /var/www/seoom/.env | grep MASTER_DOMAIN
```

다음과 같이 나와야 해요:
```
MASTER_DOMAIN=seoomweb.com
```

만약 다르면 수정:

```bash
sudo nano /var/www/seoom/.env
```

`MASTER_DOMAIN=seoomweb.com`으로 설정하고 저장

### 3단계: 캐시 클리어

```bash
sudo -u www-data php artisan config:clear
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:clear
sudo -u www-data php artisan route:cache
```

### 4단계: 브라우저에서 다시 접속

`https://seoomweb.com`으로 다시 접속해 보세요.

---

## 📋 전체 명령어 (순서대로)

```bash
# 1. MySQL 접속
sudo mysql -u root

# MySQL 프롬프트에서:
USE seoom;
INSERT INTO sites (name, slug, domain, plan, status, is_master_site, created_at, updated_at)
VALUES ('SEOom Builder', 'master', 'seoomweb.com', 'premium', 'active', 1, NOW(), NOW());
EXIT;

# 2. .env 파일 확인
sudo cat /var/www/seoom/.env | grep MASTER_DOMAIN

# 3. 캐시 클리어
sudo -u www-data php artisan config:clear
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:clear
sudo -u www-data php artisan route:cache
```

---

## 💡 참고

- 마스터 사이트는 `seoomweb.com` 도메인 자체를 사용해요
- `is_master_site = 1`로 설정되어야 해요
- `plan = 'premium'`으로 설정되어야 모든 기능을 사용할 수 있어요

---

**위 명령어들을 실행한 뒤 브라우저에서 다시 접속해 보세요!**

