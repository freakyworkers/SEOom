# 로컬 파일을 서버로 업로드하기

**방법:** WinSCP나 scp를 사용해서 파일을 직접 업로드

---

## 🔧 방법 1: WinSCP 사용 (가장 쉬움)

### 1단계: WinSCP 실행

1. WinSCP 실행
2. 서버 연결:
   - **호스트 이름**: `54.180.2.108`
   - **사용자 이름**: `ubuntu`
   - **고급** → **인증** → **개인 키 파일**: `.ppk` 파일 선택
   - **로그인** 클릭

### 2단계: 파일 업로드

1. **왼쪽**: 로컬 컴퓨터
   - `C:\Users\kangd\Desktop\01.seoom\SEOom\app\Models\Site.php` 찾기

2. **오른쪽**: 서버
   - `/var/www/seoom/app/Models/` 폴더로 이동

3. **파일 드래그 앤 드롭**
   - 로컬의 `Site.php` 파일을 서버의 `/var/www/seoom/app/Models/` 폴더로 드래그

4. **덮어쓰기 확인**
   - "덮어쓰기" 또는 "Overwrite" 선택

### 3단계: 소유권 확인

서버 터미널에서:

```bash
sudo chown www-data:www-data /var/www/seoom/app/Models/Site.php
```

Enter 키 누르기

---

## 🔧 방법 2: scp 사용 (명령어)

### PowerShell에서 실행:

```powershell
scp -i "C:\Users\kangd\Desktop\세움배포파일\seoom-key.pem" "C:\Users\kangd\Desktop\01.seoom\SEOom\app\Models\Site.php" ubuntu@54.180.2.108:/var/www/seoom/app/Models/Site.php
```

Enter 키 누르기

### 소유권 변경:

서버 터미널에서:

```bash
sudo chown www-data:www-data /var/www/seoom/app/Models/Site.php
```

Enter 키 누르기

---

## ✅ 업로드 후 확인

서버 터미널에서:

```bash
grep -n "Schema::hasTable" /var/www/seoom/app/Models/Site.php
```

Enter 키 누르면 수정된 내용이 보여요.

---

## 🎯 업로드 후 다시 시도

```bash
sudo -u www-data php artisan migrate --force
```

Enter 키 누르기

---

## 💡 추천 방법

**WinSCP 사용**이 가장 쉬워요:
- 드래그 앤 드롭만 하면 돼요
- 파일 탐색이 쉬워요
- 시각적으로 확인 가능해요

---

**WinSCP로 파일을 업로드하세요!**

