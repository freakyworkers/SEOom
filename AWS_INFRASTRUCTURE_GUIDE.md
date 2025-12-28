# AWS 인프라 구성 가이드

## 📋 개요

이 문서는 SEOom Builder 서비스의 AWS 인프라 구성 정보를 정리합니다.
Auto Scaling을 위한 5단계 마이그레이션이 완료되었습니다.

---

## 🖥️ 1. EC2 인스턴스

| 항목 | 값 |
|------|-----|
| **인스턴스 이름** | seoom-builder |
| **인스턴스 ID** | i-0511b638226f346d0 |
| **인스턴스 타입** | t3.medium (2 vCPU, 4GB RAM) |
| **퍼블릭 IP** | 52.79.104.130 |
| **프라이빗 IP** | 172.31.38.145 |
| **가용 영역** | ap-northeast-2c |
| **AMI** | seoom-web-ami (Auto Scaling용) |
| **키 페어** | seoom-key |
| **보안 그룹** | seoom-sg |

### SSH 접속 명령어
```bash
ssh -i "seoom-key.pem" ubuntu@52.79.104.130
```

---

## 🗄️ 2. Amazon RDS (MySQL 데이터베이스)

| 항목 | 값 |
|------|-----|
| **인스턴스 이름** | seoom-db |
| **엔진** | MySQL |
| **엔드포인트** | `seoom-db.c720m22q6qya.ap-northeast-2.rds.amazonaws.com` |
| **포트** | 3306 |
| **데이터베이스 이름** | seoom |
| **마스터 사용자명** | admin |
| **마스터 비밀번호** | `Tpdk1021!` |
| **리전** | ap-northeast-2 (서울) |

### Laravel .env 설정
```env
DB_CONNECTION=mysql
DB_HOST=seoom-db.c720m22q6qya.ap-northeast-2.rds.amazonaws.com
DB_PORT=3306
DB_DATABASE=seoom
DB_USERNAME=admin
DB_PASSWORD=Tpdk1021!
```

---

## 📦 3. Amazon S3 (파일 스토리지)

| 항목 | 값 |
|------|-----|
| **버킷 이름** | seoom-files-bucket |
| **리전** | ap-northeast-2 (서울) |
| **퍼블릭 액세스** | 활성화됨 |

### IAM 사용자 (S3 액세스용)

| 항목 | 값 |
|------|-----|
| **사용자 이름** | seoom-s3-user |
| **정책** | AmazonS3FullAccess |
| **Access Key ID** | `[보안상 생략 - .env 파일 참조]` |
| **Secret Access Key** | `[보안상 생략 - .env 파일 참조]` |

### Laravel .env 설정
```env
AWS_ACCESS_KEY_ID=[서버의 .env 파일 참조]
AWS_SECRET_ACCESS_KEY=[서버의 .env 파일 참조]
AWS_DEFAULT_REGION=ap-northeast-2
AWS_BUCKET=seoom-files-bucket
```

---

## 🚀 4. Amazon ElastiCache (Valkey/Redis)

| 항목 | 값 |
|------|-----|
| **캐시 이름** | seoom-cache |
| **엔진** | Valkey (Redis 호환, 33% 저렴) |
| **엔드포인트** | `seoom-cache-ndi3hn.serverless.apn2.cache.amazonaws.com` |
| **포트** | 6379 |
| **암호화** | TLS 활성화 (in-transit encryption) |
| **VPC** | vpc-0ad87484b53398918 |
| **보안 그룹** | sg-0ac91ab2a6df012af (default) |

### Laravel .env 설정
```env
REDIS_HOST=seoom-cache-ndi3hn.serverless.apn2.cache.amazonaws.com
REDIS_PASSWORD=null
REDIS_PORT=6379
REDIS_SCHEME=tls
REDIS_CLIENT=predis

# 현재 file로 설정됨 (ElastiCache Serverless 호환성 문제로 인해)
CACHE_DRIVER=file
SESSION_DRIVER=file
```

> **참고:** ElastiCache Serverless는 `SELECT` 명령을 지원하지 않아 현재 `CACHE_DRIVER=file`로 설정되어 있습니다.

---

## ⚖️ 5. Application Load Balancer (ALB)

| 항목 | 값 |
|------|-----|
| **ALB 이름** | seoom-web-alb |
| **DNS 이름** | `seoom-web-alb-455626732.ap-northeast-2.elb.amazonaws.com` |
| **스키마** | Internet-facing |
| **IP 유형** | IPv4 |
| **가용 영역** | ap-northeast-2a, ap-northeast-2b, ap-northeast-2c |
| **보안 그룹** | sg-0ac91ab2a6df012af (default) |
| **리스너** | HTTP:80 → seoom-web-tg |

### 대상 그룹 (Target Group)

| 항목 | 값 |
|------|-----|
| **이름** | seoom-web-tg |
| **프로토콜** | HTTP |
| **포트** | 80 |
| **대상 유형** | instance |
| **헬스 체크 경로** | / |

### Laravel .env 설정
```env
ALB_DNS=seoom-web-alb-455626732.ap-northeast-2.elb.amazonaws.com
FORCE_HTTPS=true
```

---

## 📈 6. Auto Scaling Group (ASG)

| 항목 | 값 |
|------|-----|
| **ASG 이름** | seoom-web-asg |
| **시작 템플릿** | seoom-web-template |
| **최소 용량** | 1 |
| **원하는 용량** | 1 |
| **최대 용량** | 3 |
| **가용 영역** | ap-northeast-2a, ap-northeast-2b |
| **대상 그룹** | seoom-web-tg |
| **스케일링 정책** | CPU 사용률 50% 기준 자동 확장 |

---

## 🔐 7. 보안 그룹 설정

### seoom-sg (EC2용)
| 유형 | 프로토콜 | 포트 | 소스 |
|------|---------|------|------|
| SSH | TCP | 22 | 0.0.0.0/0 |
| HTTP | TCP | 80 | 0.0.0.0/0 |
| HTTPS | TCP | 443 | 0.0.0.0/0 |
| MySQL | TCP | 3306 | sg-0ac91ab2a6df012af |

### default (ALB, RDS, ElastiCache용)
| 유형 | 프로토콜 | 포트 | 소스 |
|------|---------|------|------|
| All Traffic | All | All | sg-0ac91ab2a6df012af (self) |
| HTTP | TCP | 80 | 0.0.0.0/0 |

---

## 🌐 8. 도메인 및 DNS (Cloudflare)

### Cloudflare 계정
| 항목 | 값 |
|------|-----|
| **이메일** | seoomweb@gmail.com |
| **비밀번호** | Qkqh090909! |

### DNS 설정 (ALB 연결 시)
도메인 연결 시 자동으로 다음 CNAME 레코드가 생성됩니다:
- `@` (루트) → ALB DNS
- `www` → ALB DNS
- `*` (와일드카드) → ALB DNS

---

## 💰 9. 예상 비용 (월간)

| 서비스 | 예상 비용 |
|--------|----------|
| **EC2 t3.medium** | ~$30-40 |
| **RDS db.t3.micro** | ~$15-20 |
| **S3** | 사용량에 따라 변동 (~$1-5) |
| **ElastiCache Serverless** | 사용량에 따라 변동 (~$5-15) |
| **ALB** | ~$20-25 |
| **Data Transfer** | 사용량에 따라 변동 |
| **총 예상 비용** | **~$70-105/월** |

---

## 📂 10. 서버 파일 위치

| 항목 | 경로 |
|------|------|
| **Laravel 프로젝트** | /var/www/seoom |
| **환경 설정 파일** | /var/www/seoom/.env |
| **Apache 설정** | /etc/apache2/ |
| **PHP 설정** | /etc/php/8.2/ |

---

## 🔧 11. 주요 명령어

### 배포 명령어
```bash
cd /var/www/seoom
sudo git pull origin main
sudo php artisan config:clear
sudo php artisan cache:clear
```

### 서비스 재시작
```bash
sudo systemctl restart apache2
sudo systemctl restart php8.2-fpm
```

### 캐시 클리어
```bash
sudo php artisan config:clear
sudo php artisan cache:clear
sudo php artisan view:clear
sudo php artisan route:clear
```

---

## ⚠️ 주의사항

1. **비밀번호 보안**: 이 문서에 포함된 비밀번호와 액세스 키는 민감한 정보입니다. 안전하게 보관하세요.
2. **키 로테이션**: AWS IAM 액세스 키는 정기적으로 교체하는 것이 좋습니다.
3. **Auto Scaling 인스턴스**: ASG에서 새로 생성되는 인스턴스는 AMI 기준이므로, 최신 코드/설정이 반영되려면 AMI를 업데이트해야 합니다.
4. **ElastiCache**: 현재 `CACHE_DRIVER=file`로 설정되어 있어 Redis 캐시를 사용하지 않습니다. 추후 설정 조정 필요.

---

## 📅 작업 이력

| 날짜 | 작업 내용 |
|------|----------|
| 2025-12-28 | 1단계: EC2 인스턴스 업그레이드 (t3.micro → t3.medium) |
| 2025-12-28 | 2단계: Amazon RDS 생성 + DB 마이그레이션 |
| 2025-12-28 | 3단계: Amazon S3 설정 + IAM 사용자 생성 |
| 2025-12-28 | 4단계: ElastiCache (Valkey) 설정 |
| 2025-12-28 | 5단계: ALB + Auto Scaling Group 구성 |
| 2025-12-28 | HTTPS 강제 설정 (FORCE_HTTPS=true) |
| 2025-12-28 | Cloudflare DNS 자동화 (ALB CNAME 연결) |

---

**문서 작성일:** 2025년 12월 28일

