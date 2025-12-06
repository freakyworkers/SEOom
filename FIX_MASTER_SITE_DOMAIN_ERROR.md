# 마스터 사이트 도메인 에러 해결 가이드

## 🔴 문제 상황

마스터 콘솔에서 마스터 사이트를 생성했지만, `https://seoomweb.com` 접속 시 여전히 에러가 발생해요.

**에러 메시지:**
```
Missing required parameter for [Route: home] [URI: site/{site}] [Missing parameter: site]
```

## 🔍 원인 분석

1. `ResolveSiteByDomain` 미들웨어가 `seoomweb.com`을 마스터 도메인으로 인식하지 못하고 있어요
2. `.env` 파일의 `MASTER_DOMAIN` 설정이 `seoomweb.com`이 아닐 수 있어요
3. 마스터 사이트의 도메인이 `seoomweb.com`으로 설정되어 있지만, 미들웨어가 이를 찾지 못하고 있어요

## ✅ 해결 방법

### 1단계: .env 파일 확인 및 수정

서버에서 `.env` 파일을 확인하고 수정하세요:

```bash
sudo nano /var/www/seoom/.env
```

다음 항목을 확인/수정:

```env
APP_URL=https://seoomweb.com
MASTER_DOMAIN=seoomweb.com
```

저장 후 나가기: `Ctrl + X`, `Y`, `Enter`

### 2단계: 코드 업데이트 (Git Pull)

로컬에서 수정한 코드를 서버에 반영하세요:

```bash
cd /var/www/seoom
sudo git pull origin main
```

### 3단계: 캐시 클리어

```bash
sudo -u www-data php artisan config:clear
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:clear
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:clear
```

### 4단계: 마스터 사이트 도메인 확인

MySQL에서 마스터 사이트의 도메인이 올바르게 설정되어 있는지 확인:

```bash
sudo mysql -u root
```

```sql
USE seoom;
SELECT id, name, slug, domain, is_master_site, status FROM sites WHERE is_master_site = 1;
EXIT;
```

다음과 같이 나와야 해요:
- `domain`: `seoomweb.com`
- `is_master_site`: `1`
- `status`: `active`

만약 다르면 수정:

```sql
UPDATE sites SET domain = 'seoomweb.com' WHERE is_master_site = 1;
EXIT;
```

### 5단계: 브라우저에서 다시 접속

`https://seoomweb.com`으로 다시 접속해 보세요.

---

## 📋 전체 명령어 (순서대로)

```bash
# 1. .env 파일 수정
sudo nano /var/www/seoom/.env
# APP_URL=https://seoomweb.com
# MASTER_DOMAIN=seoomweb.com
# 저장 후 나가기

# 2. Git Pull
cd /var/www/seoom
sudo git pull origin main

# 3. 캐시 클리어
sudo -u www-data php artisan config:clear
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:clear
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:clear

# 4. 마스터 사이트 도메인 확인 (필요시)
sudo mysql -u root
# USE seoom;
# SELECT id, name, slug, domain, is_master_site, status FROM sites WHERE is_master_site = 1;
# UPDATE sites SET domain = 'seoomweb.com' WHERE is_master_site = 1;
# EXIT;
```

---

## 💡 참고

- 미들웨어가 마스터 사이트의 도메인도 자동으로 인식하도록 수정했어요
- `.env` 파일의 `MASTER_DOMAIN`과 마스터 사이트의 `domain` 필드가 일치해야 해요
- 캐시를 클리어하지 않으면 변경사항이 반영되지 않을 수 있어요

---

**위 명령어들을 실행한 뒤 브라우저에서 다시 접속해 보세요!**

