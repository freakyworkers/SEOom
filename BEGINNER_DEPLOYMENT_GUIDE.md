# 🚀 SEOom Builder 배포 가이드 (초보자용)

**대상:** 중학생도 따라할 수 있는 쉬운 가이드  
**예상 시간:** 2-3시간  
**필요한 것:** 컴퓨터, 인터넷 연결, 이메일 주소

---

## 📋 목차

1. [준비하기](#1-준비하기)
2. [AWS 계정 만들기](#2-aws-계정-만들기)
3. [Cloudflare 계정 만들기](#3-cloudflare-계정-만들기)
4. [도메인 준비하기](#4-도메인-준비하기)
5. [AWS EC2 서버 만들기](#5-aws-ec2-서버-만들기)
6. [서버에 프로그램 설치하기](#6-서버에-프로그램-설치하기)
7. [Cloudflare 설정하기](#7-cloudflare-설정하기)
8. [완료 확인하기](#8-완료-확인하기)

---

## 1. 준비하기

### 필요한 것들

✅ **이메일 주소** (Gmail, 네이버 메일 등)  
✅ **신용카드 또는 체크카드** (AWS는 처음 1년 무료, Cloudflare는 완전 무료)  
✅ **인내심** (처음이면 2-3시간 걸릴 수 있어요)

### 알아두면 좋은 것

- AWS는 처음 1년간 **무료**로 사용할 수 있어요 (Free Tier)
- Cloudflare는 **완전 무료**예요
- 도메인은 1년에 약 1만원 정도 (선택사항)

---

## 2. AWS 계정 만들기

### 2-1. AWS 웹사이트 접속

1. 브라우저를 열고 주소창에 입력: `https://aws.amazon.com/ko/`
2. 오른쪽 위에 있는 **"계정 만들기"** 버튼 클릭

### 2-2. 이메일로 계정 만들기

1. **이메일 주소** 입력 (예: `yourname@gmail.com`)
2. **계정 이름** 입력 (예: `My SEOom Builder`)
3. **비밀번호** 입력 (강력한 비밀번호 사용!)
4. **"계정 및 계속"** 버튼 클릭

### 2-3. 연락처 정보 입력

1. **이름** 입력
2. **전화번호** 입력
3. **국가/지역** 선택: `대한민국`
4. **주소** 입력
5. **"계속"** 버튼 클릭

### 2-4. 결제 정보 입력

⚠️ **중요:** AWS는 처음 1년간 무료지만, 카드 정보는 필요해요.  
실제로 돈이 빠져나가지 않도록 주의하세요!

1. **신용카드 또는 체크카드** 정보 입력
   - 카드 번호
   - 만료일
   - 카드 소유자 이름
   - 청구 주소
2. **"보안 확인"** 클릭 (자동으로 체크됨)
3. **"계정 만들기 및 계속"** 버튼 클릭

### 2-5. 전화번호 인증

1. **전화번호 선택** (이미 입력한 번호 또는 새 번호)
2. **"문자 메시지(SMS)로 코드 보내기"** 선택
3. **"코드 보내기"** 버튼 클릭
4. 받은 **인증 코드** 입력
5. **"코드 확인"** 버튼 클릭

### 2-6. 지원 플랜 선택

1. **"무료 지원 - 기본 플랜"** 선택 (완전 무료)
2. **"계속"** 버튼 클릭

### 2-7. 로그인

1. 이메일과 비밀번호로 **로그인**
2. 완료! 🎉

---

## 3. Cloudflare 계정 만들기

### 3-1. Cloudflare 웹사이트 접속

1. 브라우저를 열고 주소창에 입력: `https://dash.cloudflare.com/sign-up`
2. 또는 `https://www.cloudflare.com/` 접속 후 **"가입"** 클릭

### 3-2. 계정 만들기

1. **이메일 주소** 입력
2. **비밀번호** 입력 (최소 8자 이상)
3. **"가입"** 버튼 클릭

### 3-3. 이메일 인증

1. 이메일함 확인
2. Cloudflare에서 보낸 이메일 열기
3. **"이메일 확인"** 버튼 클릭

### 3-4. 완료!

Cloudflare는 **완전 무료**예요! 카드 정보도 필요 없어요! 🎉

---

## 4. 도메인 준비하기

### 옵션 1: 도메인 구매하기 (권장)

**추천 사이트:**
- 가비아 (https://www.gabia.com/) - 한국어 지원, 쉬움
- 후이즈 (https://whois.co.kr/) - 한국어 지원
- Namecheap (https://www.namecheap.com/) - 영어, 저렴함

**가격:** 1년에 약 1만원~2만원

**구매 방법:**
1. 사이트 접속
2. 원하는 도메인 검색 (예: `myseoom.com`)
3. 장바구니에 담기
4. 결제하기

**⚠️ 중요: 네임서버 설정**
- 구매할 때 **"가비아 네임서버 사용"**으로 선택하세요!
- 나중에 Cloudflare 설정이 완료되면 "타사 네임서버 사용"으로 변경할 거예요
- 지금은 아직 Cloudflare 네임서버 주소를 모르니까요!

5. 완료!

### 옵션 2: 무료 도메인 사용하기

**Freenom** (https://www.freenom.com/) - 완전 무료
- `.tk`, `.ml`, `.ga` 등 무료 도메인 제공
- 다만 신뢰도가 낮을 수 있어요

### 옵션 3: 나중에 구매하기

일단 서버만 만들고, 나중에 도메인을 연결할 수도 있어요!

---

## 5. AWS EC2 서버 만들기

### 5-1. AWS 콘솔 접속

1. 브라우저에서 `https://console.aws.amazon.com/` 접속
2. 로그인

### 5-2. EC2 서비스 찾기

1. 상단 검색창에 **"EC2"** 입력
2. **"EC2"** 클릭

### 5-3. 리전(지역) 선택

1. 오른쪽 위에서 **"서울"** 선택 (ap-northeast-2)
   - 한국에서 가장 가까워서 빠르고 저렴해요!

### 5-4. 인스턴스 시작

1. **"인스턴스 시작"** 큰 버튼 클릭

### 5-5. 이름 설정

1. **"이름"** 입력란에: `seoom-builder` 입력

### 5-6. 운영체제 선택

1. **"애플리케이션 및 OS 이미지"** 섹션에서
2. **"Ubuntu"** 선택
3. 버전: **"Ubuntu Server 22.04 LTS"** 선택
   - ⚠️ **중요:** 여러 개가 보일 수 있어요!
   - ✅ **"Ubuntu Server 22.04 LTS (HVM), SSD Volume Type"** 선택
   - ❌ "Ubuntu Server 22.04 LTS (HVM) with SQL Server 2022 Standard" 선택하지 마세요!
   - SQL Server는 필요 없어요 (우리는 MySQL을 사용해요)
4. 아키텍처: **"64-bit (x86)"** 선택
   - Arm 아키텍처는 선택하지 마세요!

### 5-7. 인스턴스 유형 선택

1. **"인스턴스 유형"** 섹션에서
2. **"t2.micro"** 또는 **"t3.micro"** 선택

**Free Tier 대상인 경우:**
- ✅ **"Free tier 사용 가능"** 표시가 보이면 → **"t2.micro"** 선택 (1년간 무료!)
- ✅ **"Free tier 사용 가능"** 표시가 보이면 → **"t3.micro"** 선택도 가능 (1년간 무료!)

**Free Tier 대상이 아닌 경우 (이전에 AWS를 사용한 적이 있는 경우):**
- ⚠️ **"Free tier 사용 가능"** 표시가 안 보여요
- ✅ **"t3.micro"** 선택 (가장 작은 유형)
- 💰 **비용 발생:** 약 월 7,000원~10,000원 정도 (사용량에 따라 다름)
- ⚠️ 사용하지 않을 때는 반드시 **인스턴스를 중지**하세요! (비용 절약)

**추천:**
- 처음 AWS를 사용하는 경우: **t2.micro** (무료)
- 이전에 AWS를 사용한 적이 있는 경우: **t3.micro** (유료, 하지만 가장 작고 저렴함)

### 5-8. 키 페어 만들기

⚠️ **중요:** 이건 나중에 서버에 접속할 때 필요한 열쇠예요!

1. **"키 페어"** 섹션에서
2. **"새 키 페어 생성"** 클릭
3. **"키 페어 이름"** 입력: `seoom-key`
4. **"키 페어 유형"** 선택: `RSA`
5. **"프라이빗 키 파일 형식"** 선택: `.pem`
6. **"키 페어 생성"** 버튼 클릭
7. ⚠️ **파일이 자동으로 다운로드됩니다!**
   - 이 파일을 **잃어버리면 안 돼요!**
   - 안전한 곳에 보관하세요!

### 5-9. 네트워크 설정

1. **"네트워크 설정"** 섹션에서
2. **"편집"** 버튼 클릭
3. **"퍼블릭 IP 자동 할당"** 선택: `활성화`
4. **"보안 그룹"** 섹션에서:
   - **"새 보안 그룹 생성"** 선택
   - **"이름"** 입력: `seoom-sg`
   - **"인바운드 보안 그룹 규칙"** 섹션:
     - **"규칙 추가"** 클릭
     - **"유형"**: `SSH` 선택
     - **"소스"**: `내 IP` 선택
     - **"규칙 추가"** 클릭
     - **"유형"**: `HTTP` 선택
     - **"소스"**: `0.0.0.0/0` 선택 (모두 허용)
     - **"규칙 추가"** 클릭
     - **"유형"**: `HTTPS` 선택
     - **"소스"**: `0.0.0.0/0` 선택 (모두 허용)

### 5-10. 스토리지 설정

1. **"스토리지 구성"** 섹션에서
2. **"크기"**: `20` GB 입력
   - **Free Tier 대상인 경우**: 월 30GB까지 무료 (20GB는 무료 티어 범위 내)
   - **Free Tier 대상이 아닌 경우**: 약 월 2,000원~3,000원 정도 (20GB 기준)
3. **"볼륨 유형"**: `gp3` 선택
   - gp3가 가장 최신이고 성능이 좋아요
   - gp2보다 저렴해요

### 5-11. 인스턴스 시작!

1. 오른쪽 아래 **"인스턴스 시작"** 버튼 클릭
2. 잠시 기다리면 **"인스턴스 시작"** 완료 메시지가 나와요!
   - ✅ **"성공"** 메시지와 함께 인스턴스 ID가 표시돼요
   - 예: `인스턴스를 시작했습니다. (i-0511b638226f346d0)`

### 5-12. 퍼블릭 IP 주소 확인

1. **"모든 인스턴스 보기"** 버튼 클릭 (오른쪽 아래 주황색 버튼)
   - 또는 상단 메뉴에서 **"인스턴스"** 클릭
2. 인스턴스 목록에서 방금 만든 인스턴스 찾기
   - 이름: `seoom-builder`
   - 인스턴스 상태가 **"실행 중"**이 될 때까지 기다리기 (1-2분)
3. 인스턴스를 클릭하면 상세 정보가 보여요
4. **"퍼블릭 IPv4 주소"** 복사해두세요!
   - 예: `54.123.45.67`
   - 이게 서버 주소예요!
   - ⚠️ **중요:** 이 주소를 메모해두세요!

---

## 6. 서버에 프로그램 설치하기

### 6-1. 서버에 접속하기 (Windows)

#### 방법 1: Windows Terminal 사용 (권장! 가장 쉬움!)

✅ **추천:** Windows Terminal이 가장 쉬워요! 추가 다운로드도 필요 없어요!

1. **Windows Terminal** 실행
   - Windows 키 누르기 → "Windows Terminal" 검색 → 실행
   - 또는: `Win + X` → "Windows Terminal" 선택

2. **PowerShell 탭 선택** (기본으로 열림)

3. **키 파일 권한 설정**
   ```powershell
   # 키 파일 경로를 실제 경로로 변경하세요!
   icacls "C:\Users\사용자이름\Downloads\seoom-key.pem" /inheritance:r
   icacls "C:\Users\사용자이름\Downloads\seoom-key.pem" /grant:r "%USERNAME%:R"
   ```
   - `사용자이름`을 실제 사용자 이름으로 변경하세요
   - 키 파일이 다른 위치에 있으면 경로도 변경하세요
   - **예시 (키 파일이 `C:\Users\kangd\Desktop\세움배포파일\seoom-key.pem`에 있는 경우):**
     ```powershell
     icacls "C:\Users\kangd\Desktop\세움배포파일\seoom-key.pem" /inheritance:r
     icacls "C:\Users\kangd\Desktop\세움배포파일\seoom-key.pem" /grant:r "%USERNAME%:R"
     ```

4. **서버 접속**
   ```powershell
   # 퍼블릭IP주소를 실제 IP 주소로 변경하세요!
   ssh -i "C:\Users\사용자이름\Downloads\seoom-key.pem" ubuntu@퍼블릭IP주소
   ```
   - `사용자이름`과 경로를 실제 경로로 변경하세요
   - **예시 (키 파일이 `C:\Users\kangd\Desktop\세움배포파일\seoom-key.pem`에 있고, IP가 `54.123.45.67`인 경우):**
     ```powershell
     ssh -i "C:\Users\kangd\Desktop\세움배포파일\seoom-key.pem" ubuntu@54.123.45.67
     ```

5. **첫 접속 시 확인 메시지**
   - `Are you sure you want to continue connecting (yes/no)?` → `yes` 입력
   - 비밀번호는 필요 없어요 (키 파일로 인증)

#### 방법 2: PuTTY 사용 (선택사항)

⚠️ **주의:** `putty.org`는 공식 사이트가 아니에요! 이상한 페이지가 나올 수 있어요!

1. **PuTTY 다운로드 (올바른 사이트)**
   - ✅ **공식 다운로드 페이지**: https://www.chiark.greenend.org.uk/~sgtatham/putty/latest.html
   - 또는: https://github.com/putty/putty/releases/latest
   - ❌ **피해야 할 사이트**: `putty.org` (공식 사이트가 아님!)
   - **"64-bit x86 installer"** 또는 **"64-bit installer"** 다운로드
   - 파일명: `putty-64bit-X.XX-installer.msi`

2. **PuTTY 설치**
   - 다운로드한 `.msi` 파일 실행
   - 설치 마법사 따라하기

3. **키 파일 변환**
   - **PuTTYgen** 실행 (PuTTY 설치 시 함께 설치됨)
   - **"Load"** 클릭 → `.pem` 파일 선택
   - **"Save private key"** 클릭 → `.ppk` 파일로 저장
   - ⚠️ **중요:** `.ppk` 파일을 안전한 곳에 보관하세요!

4. **PuTTY로 접속**
   - PuTTY 실행
   - **"Host Name"**: `ubuntu@퍼블릭IP주소` 입력
   - **"Port"**: `22`
   - 왼쪽 메뉴에서 **"Connection"** → **"SSH"** → **"Auth"** 클릭
   - **"Browse"** 클릭 → `.ppk` 파일 선택
   - **"Open"** 클릭
   - **"예"** 클릭 (경고 창)

1. **Windows Terminal** 실행
2. PowerShell 열기
3. 다음 명령어 입력:

```powershell
# 키 파일 권한 설정
icacls "C:\Users\사용자이름\Downloads\seoom-key.pem" /inheritance:r
icacls "C:\Users\사용자이름\Downloads\seoom-key.pem" /grant:r "%USERNAME%:R"

# 서버 접속
ssh -i "C:\Users\사용자이름\Downloads\seoom-key.pem" ubuntu@퍼블릭IP주소
```

### 6-2. 서버 업데이트

접속이 성공하면 다음 명령어들을 하나씩 입력하세요:

```bash
# 시스템 업데이트
sudo apt update
sudo apt upgrade -y
```

### 6-3. PHP 설치

```bash
# PHP 저장소 추가
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update

# PHP 8.3 설치
sudo apt install -y php8.3 php8.3-fpm php8.3-cli php8.3-common php8.3-mysql php8.3-xml php8.3-mbstring php8.3-curl php8.3-zip php8.3-gd php8.3-bcmath php8.3-intl

# PHP 버전 확인
php -v
```

### 6-4. Composer 설치

```bash
# Composer 다운로드
cd ~
curl -sS https://getcomposer.org/installer | php

# 전역 설치
sudo mv composer.phar /usr/local/bin/composer
sudo chmod +x /usr/local/bin/composer

# Composer 버전 확인
composer --version
```

### 6-5. MySQL 설치

```bash
# MySQL 설치
sudo apt install -y mysql-server

# MySQL 보안 설정
sudo mysql_secure_installation
```

**질문에 답변:**
- 비밀번호 정책: `0` (낮음) 입력
- root 비밀번호: 원하는 비밀번호 입력 (기억해두세요!)
- 나머지는 모두 `Y` 입력

### 6-6. 데이터베이스 만들기

```bash
# MySQL 접속
sudo mysql -u root -p
```

비밀번호 입력 후, MySQL 콘솔에서:

```sql
CREATE DATABASE seoom CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'seoom_user'@'localhost' IDENTIFIED BY '강력한비밀번호입력';
GRANT ALL PRIVILEGES ON seoom.* TO 'seoom_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 6-7. Apache 설치

```bash
# Apache 설치
sudo apt install -y apache2

# PHP-FPM 모듈 활성화
sudo a2enmod proxy_fcgi setenvif
sudo a2enconf php8.3-fpm

# 필수 모듈 활성화
sudo a2enmod rewrite
sudo a2enmod ssl
sudo a2enmod headers

# Apache 재시작
sudo systemctl restart apache2
```

### 6-8. Git 설치

```bash
sudo apt install -y git
```

### 6-9. 프로젝트 업로드

#### 방법 1: Git 사용 (권장)

```bash
# 웹 루트로 이동
cd /var/www

# Git으로 프로젝트 클론 (GitHub에 올려둔 경우)
sudo git clone https://github.com/사용자이름/seoom-builder.git seoom

# 소유권 변경
sudo chown -R www-data:www-data /var/www/seoom
sudo chmod -R 755 /var/www/seoom
```

#### 방법 2: 파일 업로드 (WinSCP 사용)

1. **WinSCP 다운로드**
   - https://winscp.net/ 접속
   - 다운로드 및 설치

2. **서버 연결**
   - **"호스트 이름"**: 퍼블릭 IP 주소
   - **"사용자 이름"**: `ubuntu`
   - **"비밀번호"**: (키 파일 사용)
   - **"고급"** → **"인증"** → **"개인 키 파일"**: `.ppk` 파일 선택
   - **"로그인"** 클릭

3. **파일 업로드**
   - 왼쪽: 로컬 컴퓨터 폴더
   - 오른쪽: 서버 (`/var/www` 폴더)
   - 프로젝트 폴더를 드래그 앤 드롭

### 6-10. Laravel 설정

```bash
# 프로젝트 폴더로 이동
cd /var/www/seoom

# 의존성 설치
sudo -u www-data composer install --no-dev --optimize-autoloader

# .env 파일 생성
sudo cp .env.example .env
sudo nano .env
```

**`.env` 파일 수정:**

```env
APP_NAME="SEOom Builder"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://도메인주소

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=seoom
DB_USERNAME=seoom_user
DB_PASSWORD=위에서설정한비밀번호

MASTER_DOMAIN=도메인주소
```

**저장:** `Ctrl + X` → `Y` → `Enter`

```bash
# 애플리케이션 키 생성
sudo -u www-data php artisan key:generate

# 스토리지 링크 생성
sudo -u www-data php artisan storage:link

# 파일 권한 설정
sudo chmod -R 775 storage bootstrap/cache
sudo chown -R www-data:www-data storage bootstrap/cache

# 마이그레이션 실행
sudo -u www-data php artisan migrate --force

# 마스터 사용자 시더 실행
sudo -u www-data php artisan db:seed --class=MasterUserSeeder

# 캐시 최적화
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache
```

### 6-11. Apache 가상 호스트 설정

```bash
sudo nano /etc/apache2/sites-available/seoom.conf
```

다음 내용 입력:

```apache
<VirtualHost *:80>
    ServerName 도메인주소
    ServerAlias www.도메인주소 *.도메인주소
    
    DocumentRoot /var/www/seoom/public

    <Directory /var/www/seoom/public>
        AllowOverride All
        Require all granted
    </Directory>

    <FilesMatch \.php$>
        SetHandler "proxy:unix:/var/run/php/php8.3-fpm.sock|fcgi://localhost"
    </FilesMatch>

    ErrorLog ${APACHE_LOG_DIR}/seoom_error.log
    CustomLog ${APACHE_LOG_DIR}/seoom_access.log combined
</VirtualHost>
```

**저장:** `Ctrl + X` → `Y` → `Enter`

```bash
# 사이트 활성화
sudo a2ensite seoom.conf
sudo a2dissite 000-default.conf

# Apache 재시작
sudo systemctl restart apache2
```

---

## 7. Cloudflare 설정하기

### 7-1. 도메인 추가

1. Cloudflare 대시보드 접속: `https://dash.cloudflare.com/`
2. **"웹사이트 추가"** 클릭
3. 도메인 입력 (예: `myseoom.com`)
4. **"플랜 선택"** → **"Free"** 선택
5. **"계속"** 클릭

### 7-2. DNS 레코드 확인

1. Cloudflare가 자동으로 DNS 레코드를 가져와요
2. 확인 후 **"계속"** 클릭

### 7-3. 네임서버 변경

⚠️ **중요:** 
- 도메인 구매할 때는 **"가비아 네임서버 사용"**으로 구매했을 거예요
- 이제 Cloudflare 설정이 완료되었으니 **"타사 네임서버 사용"**으로 변경해야 해요!

**순서:**
1. 먼저 Cloudflare에서 네임서버 주소 확인
2. 그 다음 가비아에서 네임서버 변경

#### 1단계: Cloudflare에서 네임서버 주소 확인

1. Cloudflare 대시보드 접속: https://dash.cloudflare.com/
2. 도메인 선택
3. 오른쪽에 **"네임서버"** 섹션 보기
4. 두 개의 네임서버 주소 복사
   - 예: `alice.ns.cloudflare.com`
   - 예: `bob.ns.cloudflare.com`
   - ⚠️ **두 개를 모두 복사해두세요!**

#### 2단계: 가비아에서 네임서버 변경

1. **가비아 홈페이지** 접속: https://www.gabia.com/
2. **로그인**
3. **"마이 가비아"** → **"도메인"** 클릭
4. 구매한 도메인 찾기
5. **"관리"** 또는 **"설정"** 클릭
6. **"네임서버 설정"** 또는 **"DNS 설정"** 메뉴 찾기
7. ⚠️ **"타사 네임서버 사용"** 선택 (중요!)
   - ❌ "가비아 네임서버 사용"이 아님!
   - ✅ **"타사 네임서버 사용"** 선택!
8. 네임서버 입력란에 Cloudflare 네임서버 입력:
   - **1차 네임서버**: `alice.ns.cloudflare.com` (또는 Cloudflare에서 제공한 첫 번째)
   - **2차 네임서버**: `bob.ns.cloudflare.com` (또는 Cloudflare에서 제공한 두 번째)
9. **"저장"** 또는 **"적용"** 클릭
10. 확인 메시지 확인

#### 다른 도메인 업체에서 설정하는 방법:

- **후이즈**: 도메인 관리 → 네임서버 설정 → "사용자 정의 네임서버" 선택
- **Namecheap**: Domain List → Manage → Advanced DNS → "Custom Nameservers" 선택
- **GoDaddy**: DNS → Nameservers → "Change" → "Custom" 선택

#### 3단계: 변경 완료 대기

1. **24-48시간** 기다리기 (보통 몇 시간이면 돼요)
2. Cloudflare 대시보드에서 확인
   - 도메인 옆에 **"활성"** 표시가 나오면 성공!
3. 또는 이메일로 확인
   - Cloudflare에서 "도메인이 활성화되었습니다" 이메일이 올 수 있어요

### 7-4. DNS 레코드 설정

1. Cloudflare 대시보드 → **"DNS"** 클릭
2. **"레코드 추가"** 클릭

**A 레코드 추가:**
- **"이름"**: `@` (또는 비워두기)
- **"IPv4 주소"**: AWS EC2 퍼블릭 IP 주소 입력
- **"프록시 상태"**: 🟠 **프록시됨** (주황색 구름)
- **"저장"** 클릭

**A 레코드 추가 (www):**
- **"이름"**: `www`
- **"IPv4 주소"**: AWS EC2 퍼블릭 IP 주소 입력
- **"프록시 상태"**: 🟠 **프록시됨**
- **"저장"** 클릭

**와일드카드 A 레코드 추가 (서브도메인용):**
- **"이름"**: `*`
- **"IPv4 주소"**: AWS EC2 퍼블릭 IP 주소 입력
- **"프록시 상태"**: 🟠 **프록시됨**
- **"저장"** 클릭

### 7-5. SSL/TLS 설정

1. Cloudflare 대시보드 → **"SSL/TLS"** 클릭
2. **"암호화 모드"** 선택: **"전체(엄격)"**
3. **"항상 HTTPS 사용"** 활성화

### 7-6. 보안 설정

1. **"보안"** → **"WAF"** 클릭
2. 기본 규칙 활성화

---

## 8. 완료 확인하기

### 8-1. 도메인으로 접속 확인

1. 브라우저에서 도메인 주소 입력 (예: `https://myseoom.com`)
2. 사이트가 정상적으로 보이면 성공! 🎉

### 8-2. 마스터 로그인 확인

1. `https://도메인주소/master/login` 접속
2. 마스터 계정으로 로그인
   - 이메일: `admin@seoom.com`
   - 비밀번호: `admin123`
   - ⚠️ **보안을 위해 비밀번호를 변경하세요!**

### 8-3. 사이트 생성 테스트

1. 마스터 콘솔에서 사이트 생성
2. 서브도메인으로 접속 확인 (예: `https://test-site.도메인주소`)

---

## 🆘 문제 해결

### 문제 1: 서버에 접속이 안 돼요

**해결 방법:**
- 보안 그룹에서 SSH 포트(22)가 열려있는지 확인
- 키 파일 경로가 맞는지 확인
- 퍼블릭 IP 주소가 맞는지 확인

### 문제 2: 사이트가 안 보여요

**해결 방법:**
- Apache가 실행 중인지 확인: `sudo systemctl status apache2`
- 파일 권한 확인: `sudo chmod -R 775 /var/www/seoom/storage`
- 로그 확인: `sudo tail -f /var/log/apache2/seoom_error.log`

### 문제 3: 데이터베이스 연결 오류

**해결 방법:**
- `.env` 파일의 데이터베이스 정보 확인
- MySQL이 실행 중인지 확인: `sudo systemctl status mysql`
- 데이터베이스가 생성되었는지 확인

### 문제 4: 도메인이 연결이 안 돼요

**해결 방법:**
- 네임서버 변경이 완료되었는지 확인 (24-48시간 소요)
- Cloudflare DNS 레코드가 올바른지 확인
- 브라우저 캐시 삭제 후 다시 시도

---

## 📚 추가 도움말

### 유용한 명령어

```bash
# 서버 재시작
sudo reboot

# Apache 재시작
sudo systemctl restart apache2

# MySQL 재시작
sudo systemctl restart mysql

# 로그 확인
sudo tail -f /var/log/apache2/seoom_error.log
sudo tail -f /var/www/seoom/storage/logs/laravel.log
```

### 비용 확인

- **AWS**: 처음 1년 무료 (Free Tier)
- **Cloudflare**: 완전 무료
- **도메인**: 1년에 약 1만원~2만원

### 보안 팁

1. **비밀번호는 강력하게!**
2. **마스터 계정 비밀번호 변경 필수!**
3. **정기적으로 업데이트:**
   ```bash
   sudo apt update
   sudo apt upgrade -y
   ```

---

## 🎉 완료!

축하합니다! 이제 SEOom Builder가 인터넷에서 작동하고 있어요!

**다음 단계:**
- 사이트 만들기
- 사용자 초대하기
- 커스터마이징하기

**질문이 있으면:**
- AWS 문서: https://docs.aws.amazon.com/ko_kr/
- Cloudflare 문서: https://developers.cloudflare.com/
- Laravel 문서: https://laravel.com/docs

---

**마지막 업데이트:** 2025년 1월  
**작성자:** SEOom Builder 개발팀

