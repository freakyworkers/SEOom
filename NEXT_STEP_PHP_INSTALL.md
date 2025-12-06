# 다음 단계: PHP 설치

**현재 상태:** 서버 업데이트 완료 ✅  
**다음 단계:** PHP 8.3 설치

---

## 🎯 다음 단계: PHP 설치 (6-3 단계)

서버에 접속된 상태에서 다음 명령어를 **하나씩** 입력하세요:

### 1단계: PHP 저장소 추가

```bash
sudo add-apt-repository ppa:ondrej/php -y
```

Enter 키 누르고 완료될 때까지 기다리세요.

### 2단계: 패키지 목록 업데이트

```bash
sudo apt update
```

Enter 키 누르고 완료될 때까지 기다리세요.

### 3단계: PHP 8.3 및 필요한 확장 설치

```bash
sudo apt install -y php8.3 php8.3-fpm php8.3-cli php8.3-common php8.3-mysql php8.3-xml php8.3-mbstring php8.3-curl php8.3-zip php8.3-gd php8.3-bcmath php8.3-intl
```

Enter 키 누르고 완료될 때까지 기다리세요.  
**예상 시간:** 2-3분

### 4단계: PHP 버전 확인

```bash
php -v
```

Enter 키 누르면 PHP 버전이 표시돼요.

**예상 결과:**
```
PHP 8.3.x (cli) ...
```

---

## 📋 전체 명령어 (복사해서 사용)

```bash
# PHP 저장소 추가
sudo add-apt-repository ppa:ondrej/php -y

# 패키지 목록 업데이트
sudo apt update

# PHP 8.3 및 확장 설치
sudo apt install -y php8.3 php8.3-fpm php8.3-cli php8.3-common php8.3-mysql php8.3-xml php8.3-mbstring php8.3-curl php8.3-zip php8.3-gd php8.3-bcmath php8.3-intl

# PHP 버전 확인
php -v
```

---

## 💡 팁

- 명령어는 **하나씩** 입력하세요
- 각 명령어가 완료될 때까지 기다리세요
- `-y` 옵션이 있어서 자동으로 "yes"를 입력해요

---

## ✅ 완료 확인

`php -v` 명령어를 실행했을 때:
- PHP 8.3.x 버전이 나오면 성공! ✅
- 에러가 나오면 알려주세요

---

## 🎯 다음 단계 (PHP 설치 완료 후)

1. **Composer 설치** (6-4 단계)
2. **MySQL 설치** (6-5 단계)
3. **Apache 설치** (6-7 단계)
4. **Git 설치** (6-8 단계)
5. **프로젝트 업로드** (6-9 단계)
6. **Laravel 설정** (6-10 단계)

---

**PHP 설치 명령어를 실행하세요!**

