# .env 파일 DB_USERNAME 오류 해결

**에러:** `Access denied for user 'seoom_userroot'@'localhost'`

**원인:** `.env` 파일에서 `DB_USERNAME`이 `seoom_userroot`로 설정되어 있어요 (줄바꿈 문제)

---

## 🔧 해결 방법

### 1단계: .env 파일 다시 열기

```bash
sudo nano .env
```

Enter 키 누르기

### 2단계: DB_USERNAME 확인 및 수정

**문제가 있는 부분을 찾아서:**

**잘못된 예:**
```
DB_USERNAME=seoom_userroot
```

**또는 줄바꿈이 안 된 경우:**
```
DB_USERNAME=seoom_userDB_PASSWORD=Tpdk1021!
```

**올바르게 수정:**
```
DB_USERNAME=seoom_user
DB_PASSWORD=Tpdk1021!
```

**⚠️ 중요:**
- `DB_USERNAME=seoom_user` (줄 끝에 Enter로 줄바꿈)
- `DB_PASSWORD=Tpdk1021!` (다음 줄에)

---

## 📋 올바른 .env 파일 형식

```env
APP_NAME="SEOom Builder"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_TIMEZONE=Asia/Seoul
APP_URL=http://54.180.2.108
APP_LOCALE=ko
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=ko_KR

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=seoom
DB_USERNAME=seoom_user
DB_PASSWORD=Tpdk1021!

SESSION_DRIVER=database
SESSION_LIFETIME=120
CACHE_STORE=database
QUEUE_CONNECTION=database

MAIL_MAILER=log
MAIL_FROM_ADDRESS="hello@example.com"

MASTER_DOMAIN=54.180.2.108
```

---

## 💡 nano 편집기 사용법

### 수정 방법:
1. **화살표 키**로 `DB_USERNAME` 줄로 이동
2. **`seoom_userroot`**를 찾아서 **`seoom_user`**로 수정
3. **Enter 키**로 줄바꿈 확인
4. **`DB_PASSWORD`**가 다음 줄에 있는지 확인

### 저장 및 나가기:
1. **`Ctrl + O`** 누르기 (저장)
2. **Enter** 키 누르기 (파일명 확인)
3. **`Ctrl + X`** 누르기 (나가기)

---

## ✅ 확인 사항

`.env` 파일에서 다음을 확인하세요:

- [ ] `DB_USERNAME=seoom_user` (줄 끝에 Enter)
- [ ] `DB_PASSWORD=Tpdk1021!` (다음 줄에)
- [ ] 두 줄이 합쳐져 있지 않아야 해요

---

## 🎯 수정 후 다시 시도

```bash
sudo -u www-data php artisan key:generate
```

Enter 키 누르기

---

**.env 파일을 다시 열어서 DB_USERNAME을 확인하고 수정하세요!**

