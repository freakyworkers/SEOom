# 🚀 SEOom Builder 배포 가이드 (Cloudflare 포함)

**최종 업데이트:** 2025년 1월  
**대상 환경:** AWS EC2 + Apache + PHP-FPM + MySQL + Cloudflare

---

## 📋 목차

1. [배포 개요](#배포-개요)
2. [사전 준비 사항](#사전-준비-사항)
3. [AWS EC2 서버 설정](#aws-ec2-서버-설정)
4. [서버 환경 구축](#서버-환경-구축)
5. [Cloudflare 설정](#cloudflare-설정)
6. [도메인 설정](#도메인-설정)
7. [Laravel 도메인 라우팅 구현](#laravel-도메인-라우팅-구현)
8. [보안 설정](#보안-설정)
9. [배포 자동화](#배포-자동화)
10. [배포 후 확인 사항](#배포-후-확인-사항)

---

## 🎯 배포 개요

### 배포 아키텍처

```
사용자
  ↓
Cloudflare (DNS + SSL + 보안)
  ↓
AWS EC2 (Ubuntu 22.04)
  ├── Apache + PHP-FPM 8.3
  ├── MySQL 8.0
  └── Laravel 10 Application
```

### 도메인 구조

1. **마스터 도메인**: `seoom.com` (또는 `seoom.kr`)
   - 마스터 콘솔 접근
   - 메인 사이트

2. **무료 서브도메인**: `{site-slug}.seoom.com`
   - 각 사이트별 무료 도메인
   - 예: `test-site.seoom.com`

3. **커스텀 도메인**: `example.com`
   - 사용자가 자신의 도메인 연결
   - Cloudflare를 통한 DNS 관리

---

## 📦 사전 준비 사항

### 필수 계정 및 도구

- [ ] AWS 계정 (EC2 접근 권한)
- [ ] Cloudflare 계정 (무료 플랜 가능)
- [ ] 도메인 (마스터 도메인용, 예: `seoom.com`)
- [ ] GitHub 저장소 (코드 배포용)
- [ ] SSH 클라이언트 (PuTTY, Windows Terminal 등)

### 필요한 정보

- [ ] AWS Access Key ID 및 Secret Access Key
- [ ] Cloudflare API Token
- [ ] 도메인 등록 정보
- [ ] 데이터베이스 백업 (로컬 개발 데이터)

---

## ☁️ AWS EC2 서버 설정

### 1단계: EC2 인스턴스 생성

1. **AWS 콘솔 접속**
   - https://console.aws.amazon.com/ec2 접속
   - 리전 선택 (권장: `ap-northeast-2` - 서울)

2. **인스턴스 시작**
   - "인스턴스 시작" 클릭
   - 이름: `seoom-builder-production`

3. **AMI 선택**
   - Ubuntu Server 22.04 LTS 선택

4. **인스턴스 유형**
   - `t3.small` (2 vCPU, 2GB RAM) - 최소 권장
   - 또는 `t3.medium` (2 vCPU, 4GB RAM) - 권장

5. **키 페어 생성**
   - 새 키 페어 생성
   - 이름: `seoom-builder-key`
   - 키 페어 타입: RSA
   - 프라이빗 키 파일 형식: `.pem`
   - **⚠️ 중요**: 키 파일 다운로드 후 안전하게 보관

6. **네트워크 설정**
   - VPC: 기본 VPC 사용
   - 퍼블릭 IP 자동 할당: 활성화
   - 보안 그룹: 새 보안 그룹 생성
     - 이름: `seoom-builder-sg`
     - 인바운드 규칙:
       - SSH (22): 내 IP만 허용
       - HTTP (80): 0.0.0.0/0
       - HTTPS (443): 0.0.0.0/0

7. **스토리지**
   - 볼륨 크기: 20GB (최소)
   - 볼륨 유형: gp3

8. **인스턴스 시작**
   - "인스턴스 시작" 클릭
   - 인스턴스 ID 기록

### 2단계: Elastic IP 할당 (선택, 권장)

1. **Elastic IP 생성**
   - EC2 콘솔 → Elastic IPs
   - "Elastic IP 주소 할당" 클릭
   - 할당 클릭

2. **Elastic IP 연결**
   - 할당된 IP 선택
   - "작업" → "Elastic IP 주소 연결"
   - 인스턴스 선택 후 연결

### 3단계: SSH 접속

**Windows (PowerShell 또는 CMD):**
```powershell
# 키 파일 권한 설정 (첫 접속 시)
icacls seoom-builder-key.pem /inheritance:r
icacls seoom-builder-key.pem /grant:r "%USERNAME%:R"

# SSH 접속
ssh -i seoom-builder-key.pem ubuntu@<EC2-PUBLIC-IP>
```

**Linux/Mac:**
```bash
# 키 파일 권한 설정
chmod 400 seoom-builder-key.pem

# SSH 접속
ssh -i seoom-builder-key.pem ubuntu@<EC2-PUBLIC-IP>
```

---

## 🛠️ 서버 환경 구축

### 1단계: 시스템 업데이트

```bash
sudo apt update
sudo apt upgrade -y
sudo apt install -y software-properties-common
```

### 2단계: PHP 8.3 설치

```bash
# PHP 저장소 추가
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update

# PHP 8.3 및 필수 확장 설치
sudo apt install -y \
    php8.3 \
    php8.3-fpm \
    php8.3-cli \
    php8.3-common \
    php8.3-mysql \
    php8.3-xml \
    php8.3-mbstring \
    php8.3-curl \
    php8.3-zip \
    php8.3-gd \
    php8.3-bcmath \
    php8.3-intl \
    php8.3-redis

# PHP 버전 확인
php -v
```

### 3단계: Composer 설치

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

### 4단계: MySQL 설치 및 설정

```bash
# MySQL 설치
sudo apt install -y mysql-server

# MySQL 보안 설정
sudo mysql_secure_installation
# 다음 질문에 답변:
# - 비밀번호 정책: 0 (낮음) 또는 1 (중간)
# - root 비밀번호 설정
# - 익명 사용자 제거: Y
# - 원격 root 로그인 비활성화: Y
# - test 데이터베이스 제거: Y

# 데이터베이스 생성
sudo mysql -u root -p
```

MySQL 콘솔에서:
```sql
CREATE DATABASE seoom CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'seoom_user'@'localhost' IDENTIFIED BY '강력한_비밀번호_입력';
GRANT ALL PRIVILEGES ON seoom.* TO 'seoom_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 5단계: Apache 설치 및 설정

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

### 6단계: Git 설치

```bash
sudo apt install -y git
git config --global user.name "SEOom Builder"
git config --global user.email "admin@seoom.com"
```

### 7단계: 프로젝트 배포

```bash
# 웹 루트 디렉토리로 이동
cd /var/www

# Git 저장소 클론 (또는 코드 업로드)
sudo git clone https://github.com/your-username/seoom-builder.git seoom
# 또는
# sudo scp -r /로컬/경로/seoom ubuntu@<EC2-IP>:/var/www/

# 소유권 변경
sudo chown -R www-data:www-data /var/www/seoom
sudo chmod -R 755 /var/www/seoom

# 프로젝트 디렉토리로 이동
cd /var/www/seoom
```

### 8단계: Laravel 환경 설정

```bash
# 의존성 설치
sudo -u www-data composer install --no-dev --optimize-autoloader

# .env 파일 생성
sudo cp .env.example .env
sudo nano .env
```

`.env` 파일 수정:
```env
APP_NAME="SEOom Builder"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://seoom.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=seoom
DB_USERNAME=seoom_user
DB_PASSWORD=위에서_설정한_비밀번호

# 파일 시스템
FILESYSTEM_DISK=local

# 세션 및 캐시
SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database

# 메일 설정 (Gmail 예시)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@gmail.com
MAIL_FROM_NAME="${APP_NAME}"

# Cloudflare 설정
CLOUDFLARE_ENABLED=true
CLOUDFLARE_API_TOKEN=나중에_설정
CLOUDFLARE_ZONE_ID=나중에_설정
```

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

### 9단계: Apache 가상 호스트 설정

```bash
sudo nano /etc/apache2/sites-available/seoom.conf
```

다음 내용 입력:
```apache
<VirtualHost *:80>
    ServerName seoom.com
    ServerAlias www.seoom.com *.seoom.com
    
    DocumentRoot /var/www/seoom/public

    <Directory /var/www/seoom/public>
        AllowOverride All
        Require all granted
    </Directory>

    # PHP-FPM 설정
    <FilesMatch \.php$>
        SetHandler "proxy:unix:/var/run/php/php8.3-fpm.sock|fcgi://localhost"
    </FilesMatch>

    # 로그 설정
    ErrorLog ${APACHE_LOG_DIR}/seoom_error.log
    CustomLog ${APACHE_LOG_DIR}/seoom_access.log combined
</VirtualHost>
```

```bash
# 사이트 활성화
sudo a2ensite seoom.conf
sudo a2dissite 000-default.conf

# Apache 재시작
sudo systemctl restart apache2
```

---

## ☁️ Cloudflare 설정

### 1단계: Cloudflare 계정 생성 및 도메인 추가

1. **Cloudflare 가입**
   - https://dash.cloudflare.com/sign-up 접속
   - 무료 플랜으로 가입

2. **도메인 추가**
   - "웹사이트 추가" 클릭
   - 도메인 입력: `seoom.com` (또는 사용할 도메인)
   - 플랜 선택: Free

3. **DNS 레코드 확인**
   - Cloudflare가 기존 DNS 레코드를 자동으로 가져옴
   - 확인 후 "계속" 클릭

4. **네임서버 변경**
   - Cloudflare에서 제공하는 네임서버 주소 복사
   - 도메인 등록 업체에서 네임서버 변경
   - 예: `alice.ns.cloudflare.com`, `bob.ns.cloudflare.com`

### 2단계: DNS 레코드 설정

Cloudflare 대시보드 → DNS → 레코드에서:

1. **A 레코드 (루트 도메인)**
   - 이름: `@`
   - IPv4 주소: EC2 Elastic IP (또는 퍼블릭 IP)
   - 프록시 상태: 🟠 프록시됨 (주황색 구름)
   - TTL: 자동

2. **A 레코드 (www 서브도메인)**
   - 이름: `www`
   - IPv4 주소: EC2 Elastic IP
   - 프록시 상태: 🟠 프록시됨
   - TTL: 자동

3. **와일드카드 A 레코드 (서브도메인용)**
   - 이름: `*`
   - IPv4 주소: EC2 Elastic IP
   - 프록시 상태: 🟠 프록시됨
   - TTL: 자동

### 3단계: SSL/TLS 설정

1. **SSL/TLS 모드 설정**
   - Cloudflare 대시보드 → SSL/TLS
   - 암호화 모드: **전체(엄격)** 선택
   - 이 모드는 Cloudflare와 서버 간 통신도 암호화

2. **원본 인증서 생성 (선택, 권장)**
   - SSL/TLS → 원본 서버
   - "인증서 생성" 클릭
   - 호스트 이름: `*.seoom.com`, `seoom.com`
   - 인증서 및 개인 키 다운로드

3. **서버에 SSL 인증서 설치** (원본 인증서 사용 시)
```bash
# 인증서 디렉토리 생성
sudo mkdir -p /etc/ssl/cloudflare

# 인증서 파일 업로드 후
sudo nano /etc/ssl/cloudflare/cert.pem  # 인증서 내용
sudo nano /etc/ssl/cloudflare/key.pem   # 개인 키 내용

# 권한 설정
sudo chmod 600 /etc/ssl/cloudflare/key.pem
sudo chmod 644 /etc/ssl/cloudflare/cert.pem
```

### 4단계: 보안 설정

1. **자동 HTTPS 리다이렉션**
   - SSL/TLS → Edge Certificates
   - "항상 HTTPS 사용" 활성화

2. **보안 레벨**
   - SSL/TLS → 개요
   - 보안 레벨: **중간** 또는 **높음**

3. **방화벽 규칙** (선택)
   - 보안 → WAF
   - 기본 규칙 활성화

4. **Rate Limiting** (선택, Pro 플랜 이상)
   - 보안 → Rate Limiting
   - 규칙 생성

### 5단계: Cloudflare API Token 생성

1. **API Token 생성**
   - Cloudflare 대시보드 → 내 프로필 → API 토큰
   - "토큰 생성" 클릭
   - "Zone DNS 편집" 템플릿 선택
   - 권한:
     - Zone: DNS:Edit
     - Zone: Zone:Read
   - Zone 리소스: 특정 Zone 포함 → `seoom.com` 선택
   - "계속" → "토큰 생성"
   - **⚠️ 중요**: 토큰 복사 후 안전하게 보관 (다시 볼 수 없음)

2. **Zone ID 확인**
   - Cloudflare 대시보드 → 개요
   - Zone ID 복사

### 6단계: Laravel에 Cloudflare 설정 추가

```bash
cd /var/www/seoom
sudo nano .env
```

`.env` 파일에 추가:
```env
CLOUDFLARE_ENABLED=true
CLOUDFLARE_API_TOKEN=생성한_API_토큰
CLOUDFLARE_ZONE_ID=Zone_ID
```

---

## 🌐 도메인 설정

### 무료 서브도메인 (자동 생성)

각 사이트는 자동으로 `{site-slug}.seoom.com` 형태의 서브도메인을 받습니다.

**설정 방법:**
- Cloudflare DNS에서 와일드카드 A 레코드(`*`)가 이미 설정되어 있음
- Laravel에서 도메인 기반 라우팅 구현 필요 (아래 참조)

### 커스텀 도메인 연결

사용자가 자신의 도메인을 연결할 수 있도록 지원합니다.

#### 방법 1: Cloudflare를 통한 연결 (권장)

1. **사용자가 도메인을 Cloudflare에 추가**
   - 사용자의 Cloudflare 계정에 도메인 추가
   - 네임서버 변경

2. **DNS 레코드 설정**
   - A 레코드: EC2 IP 주소
   - 프록시 상태: 🟠 프록시됨

3. **Laravel에서 도메인 등록**
   - 관리자 페이지에서 도메인 입력
   - 데이터베이스에 도메인 저장

#### 방법 2: CNAME을 통한 연결

1. **사용자 도메인에 CNAME 레코드 추가**
   - 이름: `@` 또는 `www`
   - 대상: `seoom.com` 또는 `{site-slug}.seoom.com`
   - TTL: 자동

2. **Laravel에서 도메인 등록**
   - 관리자 페이지에서 도메인 입력

---

## 🔧 Laravel 도메인 라우팅 구현

### 1단계: 도메인 기반 라우팅 미들웨어 생성

```bash
cd /var/www/seoom
sudo nano app/Http/Middleware/ResolveSiteByDomain.php
```

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Site;

class ResolveSiteByDomain
{
    /**
     * Handle an incoming request.
     * 도메인 기반으로 사이트를 찾아서 라우트에 바인딩합니다.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();
        
        // 마스터 도메인 체크 (예: seoom.com, www.seoom.com)
        $masterDomain = config('app.master_domain', 'seoom.com');
        $masterDomains = [$masterDomain, 'www.' . $masterDomain];
        
        if (in_array($host, $masterDomains)) {
            // 마스터 사이트 처리
            $masterSite = Site::getMasterSite();
            if ($masterSite) {
                $request->attributes->set('site', $masterSite);
            }
            return $next($request);
        }
        
        // 서브도메인 체크 (예: test-site.seoom.com)
        $subdomain = $this->extractSubdomain($host, $masterDomain);
        if ($subdomain) {
            $site = Site::where('slug', $subdomain)
                ->where('status', 'active')
                ->first();
            
            if ($site) {
                $request->attributes->set('site', $site);
                return $next($request);
            }
        }
        
        // 커스텀 도메인 체크
        $site = Site::where('domain', $host)
            ->where('status', 'active')
            ->first();
        
        if ($site) {
            $request->attributes->set('site', $site);
            return $next($request);
        }
        
        // 사이트를 찾을 수 없으면 404
        abort(404, 'Site not found');
    }
    
    /**
     * Extract subdomain from host
     */
    private function extractSubdomain(string $host, string $masterDomain): ?string
    {
        if (str_ends_with($host, '.' . $masterDomain)) {
            return str_replace('.' . $masterDomain, '', $host);
        }
        
        return null;
    }
}
```

### 2단계: 미들웨어 등록

```bash
sudo nano app/Http/Kernel.php
```

`$middlewareGroups['web']`에 추가:
```php
protected $middlewareGroups = [
    'web' => [
        // ... 기존 미들웨어
        \App\Http\Middleware\ResolveSiteByDomain::class,
        // ...
    ],
];
```

### 3단계: 라우트 수정

```bash
sudo nano routes/web.php
```

라우트 그룹 수정:
```php
// 도메인 기반 또는 슬러그 기반 라우팅
Route::middleware(['web'])->group(function () {
    $site = request()->attributes->get('site');
    
    if ($site) {
        // 도메인 기반 접근
        Route::prefix('')->group(function () use ($site) {
            // 기존 라우트들...
        });
    } else {
        // 슬러그 기반 접근 (기존 방식)
        Route::prefix('site/{site}')->group(function () {
            // 기존 라우트들...
        });
    }
});
```

### 4단계: config 파일에 마스터 도메인 추가

```bash
sudo nano config/app.php
```

```php
'master_domain' => env('MASTER_DOMAIN', 'seoom.com'),
```

`.env` 파일에 추가:
```env
MASTER_DOMAIN=seoom.com
```

---

## 🔒 보안 설정

### 1단계: 방화벽 설정 (UFW)

```bash
# UFW 설치 및 활성화
sudo apt install -y ufw
sudo ufw default deny incoming
sudo ufw default allow outgoing

# SSH 허용
sudo ufw allow 22/tcp

# HTTP/HTTPS 허용
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp

# 방화벽 활성화
sudo ufw enable
sudo ufw status
```

### 2단계: Cloudflare Real IP 설정

Cloudflare를 사용하면 실제 클라이언트 IP를 얻기 위해 설정이 필요합니다.

```bash
sudo nano app/Http/Middleware/TrustProxies.php
```

```php
protected $proxies = '*';

protected $headers = Request::HEADER_X_FORWARDED_FOR |
                     Request::HEADER_X_FORWARDED_HOST |
                     Request::HEADER_X_FORWARDED_PORT |
                     Request::HEADER_X_FORWARDED_PROTO |
                     Request::HEADER_X_FORWARDED_AWS_ELB;
```

### 3단계: Rate Limiting 설정

```bash
sudo nano app/Http/Kernel.php
```

```php
protected $middlewareAliases = [
    // ...
    'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
];
```

라우트에 적용:
```php
Route::middleware(['throttle:60,1'])->group(function () {
    // API 라우트 등
});
```

### 4단계: 보안 헤더 설정

```bash
sudo nano /etc/apache2/sites-available/seoom.conf
```

```apache
<VirtualHost *:80>
    # ... 기존 설정 ...
    
    # 보안 헤더
    Header always set X-Content-Type-Options "nosniff"
    Header always set X-Frame-Options "SAMEORIGIN"
    Header always set X-XSS-Protection "1; mode=block"
    Header always set Referrer-Policy "strict-origin-when-cross-origin"
</VirtualHost>
```

```bash
sudo a2enmod headers
sudo systemctl restart apache2
```

### 5단계: 파일 권한 설정

```bash
cd /var/www/seoom
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
sudo chmod -R 755 public
```

---

## 🤖 배포 자동화

### 방법 1: GitHub Actions 사용

`.github/workflows/deploy.yml` 생성:

```yaml
name: Deploy to Production

on:
  push:
    branches:
      - main

jobs:
  deploy:
    runs-on: ubuntu-latest
    
    steps:
      - name: Deploy to server
        uses: appleboy/ssh-action@master
        with:
          host: ${{ secrets.EC2_HOST }}
          username: ubuntu
          key: ${{ secrets.EC2_SSH_KEY }}
          script: |
            cd /var/www/seoom
            git pull origin main
            composer install --no-dev --optimize-autoloader
            php artisan migrate --force
            php artisan config:cache
            php artisan route:cache
            php artisan view:cache
            sudo systemctl reload apache2
```

GitHub Secrets 설정:
- `EC2_HOST`: EC2 퍼블릭 IP
- `EC2_SSH_KEY`: SSH 개인 키

### 방법 2: 배포 스크립트 사용

```bash
sudo nano /var/www/seoom/deploy.sh
```

```bash
#!/bin/bash

cd /var/www/seoom

# Git pull
git pull origin main

# 의존성 설치
composer install --no-dev --optimize-autoloader

# 마이그레이션
php artisan migrate --force

# 캐시 최적화
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 권한 설정
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

# Apache 재시작
sudo systemctl reload apache2

echo "Deployment completed!"
```

```bash
sudo chmod +x /var/www/seoom/deploy.sh
```

사용:
```bash
cd /var/www/seoom
./deploy.sh
```

---

## ✅ 배포 후 확인 사항

### 1. 기본 기능 확인

- [ ] 메인 페이지 접속 확인
- [ ] 마스터 로그인 확인
- [ ] 사이트 생성 확인
- [ ] 게시판/게시글 기능 확인
- [ ] 파일 업로드 확인

### 2. 도메인 확인

- [ ] 마스터 도메인 접속 확인 (`seoom.com`)
- [ ] 서브도메인 접속 확인 (`{site-slug}.seoom.com`)
- [ ] 커스텀 도메인 접속 확인 (연결된 경우)

### 3. SSL 확인

- [ ] HTTPS 접속 확인
- [ ] SSL 인증서 유효성 확인
- [ ] HTTP → HTTPS 리다이렉션 확인

### 4. 성능 확인

- [ ] 페이지 로딩 속도 확인
- [ ] 이미지 로딩 확인
- [ ] 데이터베이스 쿼리 확인

### 5. 보안 확인

- [ ] 방화벽 상태 확인
- [ ] 파일 권한 확인
- [ ] 로그 파일 확인

---

## 📝 추가 참고 사항

### 로그 확인

```bash
# Apache 로그
sudo tail -f /var/log/apache2/seoom_error.log
sudo tail -f /var/log/apache2/seoom_access.log

# Laravel 로그
tail -f /var/www/seoom/storage/logs/laravel.log

# PHP-FPM 로그
sudo tail -f /var/log/php8.3-fpm.log
```

### 백업 설정

```bash
# 데이터베이스 백업 스크립트
sudo nano /usr/local/bin/backup-seoom.sh
```

```bash
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/var/backups/seoom"
mkdir -p $BACKUP_DIR

# 데이터베이스 백업
mysqldump -u seoom_user -p비밀번호 seoom > $BACKUP_DIR/db_$DATE.sql

# 파일 백업
tar -czf $BACKUP_DIR/files_$DATE.tar.gz /var/www/seoom/storage/app/public

# 오래된 백업 삭제 (30일 이상)
find $BACKUP_DIR -type f -mtime +30 -delete
```

```bash
sudo chmod +x /usr/local/bin/backup-seoom.sh

# Cron 설정 (매일 새벽 2시)
sudo crontab -e
# 추가: 0 2 * * * /usr/local/bin/backup-seoom.sh
```

---

## 🎯 다음 단계

배포 완료 후:

1. **모니터링 설정**
   - 에러 추적 시스템 (Sentry 등)
   - 성능 모니터링
   - 로그 모니터링

2. **성능 최적화**
   - Redis 캐싱 도입
   - 이미지 최적화
   - CDN 연동

3. **추가 기능 개발**
   - 사용자 요청 기능
   - 관리자 페이지 개선

---

**마지막 업데이트:** 2025년 1월  
**문의:** 배포 중 문제 발생 시 이 문서를 참조하거나 개발팀에 문의하세요.

