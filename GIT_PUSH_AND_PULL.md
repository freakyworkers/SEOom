# GitHub 푸시 후 서버에서 Pull

**방법:** 로컬에서 GitHub에 푸시 → 서버에서 pull

---

## 📋 로컬에서 작업 (Windows)

### 1단계: 변경사항 확인

```powershell
git status
```

Enter 키 누르기

### 2단계: 변경사항 추가

```powershell
git add app/Models/Site.php
```

Enter 키 누르기

### 3단계: 커밋

```powershell
git commit -m "Fix: Site::getMasterSite() 테이블 존재 여부 확인 추가"
```

Enter 키 누르기

### 4단계: GitHub에 푸시

```powershell
git push origin main
```

또는:

```powershell
git push origin master
```

Enter 키 누르기

**⚠️ 중요:** 브랜치 이름을 확인하세요 (`main` 또는 `master`)

---

## 📋 서버에서 작업 (Ubuntu)

### 1단계: Git safe.directory 설정 (소유권 문제 해결)

```bash
sudo git config --global --add safe.directory /var/www/seoom
```

Enter 키 누르기

### 2단계: 프로젝트 폴더로 이동

```bash
cd /var/www/seoom
```

Enter 키 누르기

### 3단계: 변경사항 가져오기

```bash
sudo git pull origin main
```

또는:

```bash
sudo git pull origin master
```

Enter 키 누르기

**⚠️ 중요:** 브랜치 이름을 확인하세요 (`main` 또는 `master`)

### 4단계: 소유권 확인

```bash
sudo chown -R www-data:www-data /var/www/seoom
```

Enter 키 누르기

---

## 🔍 브랜치 이름 확인 방법

### 로컬에서 확인:

```powershell
git branch
```

Enter 키 누르기

### 서버에서 확인:

```bash
cd /var/www/seoom
git branch
```

Enter 키 누르기

---

## 📋 전체 순서

### 로컬 (Windows):
1. `git status` - 변경사항 확인
2. `git add app/Models/Site.php` - 파일 추가
3. `git commit -m "Fix: Site::getMasterSite() 테이블 존재 여부 확인 추가"` - 커밋
4. `git push origin main` (또는 `master`) - 푸시

### 서버 (Ubuntu):
1. `sudo git config --global --add safe.directory /var/www/seoom` - 소유권 문제 해결
2. `cd /var/www/seoom` - 프로젝트 폴더로 이동
3. `sudo git pull origin main` (또는 `master`) - 변경사항 가져오기
4. `sudo chown -R www-data:www-data /var/www/seoom` - 소유권 확인

---

## 🎯 업데이트 후 다시 시도

```bash
sudo -u www-data php artisan migrate --force
```

Enter 키 누르기

---

**로컬에서 Git 커밋 및 푸시를 먼저 하세요!**

