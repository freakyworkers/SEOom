# Git Pull 충돌 해결

**문제:** 서버에 로컬 변경사항이 있어서 pull이 안 돼요

**에러:** `Your local changes to the following files would be overwritten by merge`

---

## 🔧 해결 방법: 로컬 변경사항 버리기

GitHub에 이미 올라간 버전을 사용하는 게 좋아요!

### 방법 1: 로컬 변경사항 버리고 GitHub 버전 사용 (권장)

```bash
sudo git reset --hard origin/main
```

Enter 키 누르기

**이 명령어는:**
- 서버의 로컬 변경사항을 모두 버려요
- GitHub의 최신 버전으로 덮어써요
- 안전해요 (GitHub에 이미 올라간 버전이니까요)

### 방법 2: 로컬 변경사항 stash 후 pull

```bash
sudo git stash
sudo git pull origin main
```

Enter 키 누르기

---

## 📋 전체 순서

### 1단계: 로컬 변경사항 버리기

```bash
sudo git reset --hard origin/main
```

Enter 키 누르기

**예상 결과:**
```
HEAD is now at 082e8ae ...
```

### 2단계: 소유권 확인

```bash
sudo chown -R www-data:www-data /var/www/seoom
```

Enter 키 누르기

### 3단계: 파일 확인

```bash
grep -n "Schema::hasTable" /var/www/seoom/app/Models/Site.php
```

Enter 키 누르면 수정된 내용이 보여요.

---

## 🎯 수정 확인 후 마이그레이션 실행

```bash
sudo -u www-data php artisan migrate --force
```

Enter 키 누르기

---

## 💡 왜 이 방법을 사용하나요?

- GitHub에 이미 올라간 버전이 최신이에요
- 서버의 로컬 변경사항은 필요 없어요
- `reset --hard`로 깔끔하게 덮어쓸 수 있어요

---

**`sudo git reset --hard origin/main` 명령어를 실행하세요!**

