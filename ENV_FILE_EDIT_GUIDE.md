# .env 파일 수정 가이드

**상황:** .env 파일을 열었는데 어떤 부분을 수정해야 할지 모르겠어요

---

## 📋 수정해야 할 부분

### 1. APP_DEBUG 변경

**현재:**
```
APP_DEBUG=true
```

**변경:**
```
APP_DEBUG=false
```

**이유:** 프로덕션 환경에서는 false로 설정해야 해요

---

### 2. APP_URL 변경

**현재:**
```
APP_URL=http://localhost
```

**변경:**
```
APP_URL=http://54.180.2.108
```

**이유:** 서버의 IP 주소로 변경해야 해요 (나중에 도메인으로 변경 가능)

---

### 3. DB_USERNAME 변경

**현재:**
```
DB_USERNAME=root
```

**변경:**
```
DB_USERNAME=seoom_user
```

**이유:** 위에서 만든 데이터베이스 사용자 이름으로 변경해야 해요

---

### 4. DB_PASSWORD 추가

**현재:**
```
DB_PASSWORD=
```

**변경:**
```
DB_PASSWORD=Tpdk1021!
```

**이유:** 위에서 설정한 데이터베이스 비밀번호를 입력해야 해요

---

### 5. MASTER_DOMAIN 추가

**파일 맨 아래에 추가:**
```
MASTER_DOMAIN=54.180.2.108
```

**이유:** 마스터 도메인 설정이 필요해요 (나중에 도메인으로 변경 가능)

---

## 📝 수정 후 전체 내용 (참고용)

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

### 파일 수정:
1. **화살표 키**로 이동
2. **백스페이스** 또는 **Delete**로 삭제
3. **타이핑**해서 입력

### 저장 및 나가기:
1. **`Ctrl + O`** 누르기 (저장)
2. **Enter** 키 누르기 (파일명 확인)
3. **`Ctrl + X`** 누르기 (나가기)

---

## ✅ 수정 체크리스트

- [ ] `APP_DEBUG=true` → `APP_DEBUG=false`
- [ ] `APP_URL=http://localhost` → `APP_URL=http://54.180.2.108`
- [ ] `DB_USERNAME=root` → `DB_USERNAME=seoom_user`
- [ ] `DB_PASSWORD=` → `DB_PASSWORD=Tpdk1021!`
- [ ] 파일 맨 아래에 `MASTER_DOMAIN=54.180.2.108` 추가

---

## 🎯 수정 순서

1. 화살표 키로 `APP_DEBUG=true`로 이동
2. `true`를 `false`로 변경
3. `APP_URL=http://localhost`로 이동
4. `localhost`를 `54.180.2.108`로 변경
5. `DB_USERNAME=root`로 이동
6. `root`를 `seoom_user`로 변경
7. `DB_PASSWORD=`로 이동
8. `=` 뒤에 `Tpdk1021!` 입력
9. 파일 맨 아래로 이동 (화살표 키 아래로)
10. `MASTER_DOMAIN=54.180.2.108` 입력
11. `Ctrl + O` → Enter → `Ctrl + X`

---

**위 순서대로 수정하세요!**

