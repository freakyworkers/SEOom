# 마스터 사이트 생성 가이드

## 문제 해결

### 1. 구독 설정 404 에러
- 라우트는 정상적으로 정의되어 있어요
- 컨트롤러 파일이 있는지 확인 필요

### 2. 마스터 사이트 생성 방법

**중요:** `seoomweb.com` 자체가 마스터 사이트 역할을 해야 해요!

---

## 마스터 사이트 생성 방법

### 방법 1: 마스터 콘솔에서 생성 (권장)

1. 마스터 콘솔 접속: `https://seoomweb.com/master/login`
   - 이메일: `master@seoom.com`
   - 비밀번호: `Qkqh090909!`
2. 로그인 후 왼쪽 메뉴에서 **"마스터 사이트 관리"** 클릭
3. **"마스터 사이트 생성"** 버튼 클릭
4. 다음 정보 입력:
   - **이름**: `SEOom Builder` (또는 원하는 이름)
   - **슬러그**: `master` (또는 비워두면 자동 생성)
   - **도메인**: `seoomweb.com` ⚠️ **중요!**
5. **"생성"** 버튼 클릭

### 방법 2: MySQL에서 직접 생성

서버에서 다음 명령어 실행:

```bash
sudo mysql -u root
```

MySQL 프롬프트에서:

```sql
USE seoom;

INSERT INTO sites (name, slug, domain, plan, status, is_master_site, created_at, updated_at)
VALUES ('SEOom Builder', 'master', 'seoomweb.com', 'premium', 'active', 1, NOW(), NOW());
```

`EXIT;`로 나가기

---

## 마스터 사이트 설정 확인

생성 후 확인:

```bash
sudo mysql -u root -e "USE seoom; SELECT id, name, slug, domain, is_master_site FROM sites WHERE is_master_site = 1;"
```

---

## 구독 설정 404 에러 해결

컨트롤러 파일이 있는지 확인:

```bash
ls -la /var/www/seoom/app/Http/Controllers/Master/MasterSubscriptionSettingsController.php
```

파일이 없으면 생성해야 해요.

---

**먼저 마스터 사이트를 생성하세요!**

