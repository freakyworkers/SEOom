# .env 파일 수정 가이드 (자세한 설명)

**상황:** .env 파일에서 두 가지를 수정해야 해요

---

## 🔧 수정해야 할 부분

### 1. DB_USERNAME 수정

**현재 (잘못됨):**
```
DB_USERNAME=seoom_userroot
```

**수정 후 (올바름):**
```
DB_USERNAME=seoom_user
```

---

### 2. MASTER_DOMAIN 추가

**파일 맨 아래에 추가:**
```
MASTER_DOMAIN=52.79.104.130
```

---

## 📋 nano 편집기에서 수정하는 방법 (단계별)

### 1단계: .env 파일 열기

```bash
sudo nano .env
```

Enter 키 누르기

---

### 2단계: DB_USERNAME 수정

1. **화살표 키 (↓)**로 아래로 이동해서 `DB_USERNAME=seoom_userroot` 줄 찾기
2. **화살표 키 (→)**로 `seoom_userroot`의 `root` 부분으로 이동
3. **백스페이스** 또는 **Delete** 키로 `root` 삭제
   - `seoom_userroot` → `seoom_user`로 변경
4. **Enter 키** 누르기 (줄바꿈 확인)

---

### 3단계: MASTER_DOMAIN 추가

1. **화살표 키 (↓)**로 파일 맨 아래로 이동
2. `MAIL_FROM_ADDRESS="hello@example.com"` 다음 줄로 이동
3. **Enter 키** 누르기 (새 줄 만들기)
4. **타이핑**: `MASTER_DOMAIN=52.79.104.130`
5. **Enter 키** 누르기 (줄바꿈 확인)

---

### 4단계: 저장 및 나가기

1. **`Ctrl + O`** 누르기 (저장)
2. **Enter 키** 누르기 (파일명 확인)
3. **`Ctrl + X`** 누르기 (나가기)

---

## 📝 수정 후 올바른 형식

```env
APP_NAME="SEOom Builder"
APP_ENV=production
APP_KEY=base64:QScgT6S3hwC56UyhUs1BTTCM8v0nfXRcJ8cZYJVhSLM=
APP_DEBUG=false
APP_TIMEZONE=Asia/Seoul
APP_URL=http://52.79.104.130
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

MASTER_DOMAIN=52.79.104.130
```

---

## 💡 nano 편집기 단축키

- **화살표 키**: 커서 이동
- **백스페이스/Delete**: 삭제
- **Enter**: 줄바꿈
- **`Ctrl + O`**: 저장
- **`Ctrl + X`**: 나가기

---

## ✅ 수정 체크리스트

- [ ] `DB_USERNAME=seoom_userroot` → `DB_USERNAME=seoom_user`로 수정
- [ ] 파일 맨 아래에 `MASTER_DOMAIN=52.79.104.130` 추가
- [ ] 저장 (`Ctrl + O` → Enter)
- [ ] 나가기 (`Ctrl + X`)

---

## 🎯 수정 후 다시 시도

```bash
sudo -u www-data php artisan key:generate
```

Enter 키 누르기

---

**위 단계대로 수정하세요!**

