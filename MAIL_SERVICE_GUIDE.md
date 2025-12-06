# 메일 서비스 설정 가이드

Gmail 대신 사용할 수 있는 다양한 메일 서비스 옵션과 설정 방법입니다.

## 📧 추천 메일 서비스 비교

### 1. **Mailgun** (추천 ⭐)
- **무료 티어**: 월 5,000통 (3개월간)
- **장점**: 
  - 설정이 간단함
  - 신뢰성 높음
  - 상세한 통계 제공
  - 한국에서도 접근 가능
- **단점**: 무료 티어는 3개월만 제공
- **가격**: 무료 티어 후 $35/월부터

### 2. **SendGrid** (추천 ⭐)
- **무료 티어**: 월 100통 (무제한)
- **장점**: 
  - 영구 무료 티어 제공
  - 설정 간단
  - 트위터 계정으로 가입 가능
- **단점**: 무료 티어는 하루 100통 제한
- **가격**: 무료 티어 후 $19.95/월부터

### 3. **AWS SES** (비용 효율적)
- **무료 티어**: 없음 (하지만 매우 저렴)
- **장점**: 
  - 매우 저렴 ($0.10/1,000통)
  - AWS 사용 시 통합 용이
  - 확장성 좋음
- **단점**: 초기 설정이 복잡할 수 있음
- **가격**: $0.10/1,000통

### 4. **네이버 메일** (한국 서비스)
- **무료**: 네이버 계정 필요
- **장점**: 
  - 한국 서비스라 접근 쉬움
  - 무료
  - 설정 간단
- **단점**: 
  - 일일 발송량 제한 있음
  - 스팸 필터에 걸릴 수 있음

### 5. **카카오 메일 (Daum)** (한국 서비스)
- **무료**: 카카오 계정 필요
- **장점**: 
  - 한국 서비스
  - 무료
- **단점**: 
  - 일일 발송량 제한
  - 스팸 필터에 걸릴 수 있음

---

## 🚀 설정 방법

### 방법 1: Mailgun 사용 (가장 추천)

#### 1단계: Mailgun 가입
1. https://www.mailgun.com 접속
2. "Sign Up" 클릭
3. 이메일로 가입 (또는 Google 계정으로 가입)
4. 이메일 인증 완료

#### 2단계: 도메인 추가 (무료 티어는 샌드박스 도메인 사용 가능)
1. Mailgun 대시보드 → "Sending" → "Domains"
2. **옵션 A: 샌드박스 도메인 사용** (테스트용, 무료)
   - "Add New Domain" → "Sandbox Domain" 선택
   - 제공된 샌드박스 도메인 사용 (예: `sandbox12345.mailgun.org`)
   - **주의**: 샌드박스 도메인은 등록된 이메일로만 발송 가능
   
3. **옵션 B: 자체 도메인 추가** (프로덕션용)
   - "Add New Domain" → 자신의 도메인 입력 (예: `mail.seoomweb.com`)
   - DNS 레코드 설정 필요 (가이드 제공됨)

#### 3단계: API 키 확인
1. Mailgun 대시보드 → "Sending" → "Domain Settings"
2. "SMTP credentials" 섹션에서 확인:
   - **SMTP Hostname**: `smtp.mailgun.org`
   - **Port**: `587` (TLS) 또는 `465` (SSL)
   - **Username**: `postmaster@your-domain.mailgun.org`
   - **Password**: "Reset Password" 클릭하여 생성

#### 4단계: Laravel 설정
관리자 페이지 → 메일 설정에서 입력:
- **메일러**: `smtp`
- **호스트**: `smtp.mailgun.org`
- **포트**: `587`
- **암호화**: `tls`
- **사용자명**: `postmaster@your-domain.mailgun.org`
- **비밀번호**: Mailgun에서 생성한 비밀번호
- **발신자 이메일**: `noreply@your-domain.mailgun.org` (또는 샌드박스 도메인)

---

### 방법 2: SendGrid 사용

#### 1단계: SendGrid 가입
1. https://sendgrid.com 접속
2. "Start for free" 클릭
3. 이메일 또는 Google/Twitter 계정으로 가입
4. 이메일 인증 완료

#### 2단계: API 키 생성
1. SendGrid 대시보드 → "Settings" → "API Keys"
2. "Create API Key" 클릭
3. 이름 입력 (예: "SEOom Builder")
4. "Full Access" 또는 "Restricted Access" 선택
5. "Create & View" 클릭
6. **API 키 복사** (한 번만 표시됨!)

#### 3단계: 발신자 인증 (Sender Authentication)
1. SendGrid 대시보드 → "Settings" → "Sender Authentication"
2. "Single Sender Verification" 클릭
3. "Create a Sender" 클릭
4. 정보 입력:
   - **From Email**: 발신자 이메일 (예: `noreply@seoomweb.com`)
   - **From Name**: 발신자 이름 (예: "SEOom Builder")
   - **Reply To**: 답장 받을 이메일
5. 이메일 인증 완료

#### 4단계: Laravel 설정
관리자 페이지 → 메일 설정에서 입력:
- **메일러**: `smtp`
- **호스트**: `smtp.sendgrid.net`
- **포트**: `587`
- **암호화**: `tls`
- **사용자명**: `apikey` (고정값)
- **비밀번호**: SendGrid에서 생성한 API 키
- **발신자 이메일**: 인증한 발신자 이메일

---

### 방법 3: 네이버 메일 사용

#### 1단계: 네이버 계정 준비
- 네이버 계정이 필요합니다
- 네이버 메일 계정이 있어야 합니다

#### 2단계: 네이버 메일 SMTP 설정 확인
- 네이버 메일 → 환경설정 → POP3/IMAP 설정
- "POP3/SMTP 사용함" 체크
- **보안 메일** 설정 필요할 수 있음

#### 3단계: Laravel 설정
관리자 페이지 → 메일 설정에서 입력:
- **메일러**: `smtp`
- **호스트**: `smtp.naver.com`
- **포트**: `587`
- **암호화**: `tls`
- **사용자명**: 네이버 메일 주소 (예: `your-email@naver.com`)
- **비밀번호**: 네이버 계정 비밀번호
- **발신자 이메일**: 네이버 메일 주소

**주의사항**:
- 네이버는 일일 발송량 제한이 있습니다
- 스팸 필터에 걸릴 수 있습니다
- 보안 메일 설정이 필요할 수 있습니다

---

### 방법 4: 카카오 메일 (Daum) 사용

#### 1단계: 카카오 계정 준비
- 카카오 계정이 필요합니다
- 카카오 메일 계정이 있어야 합니다

#### 2단계: Laravel 설정
관리자 페이지 → 메일 설정에서 입력:
- **메일러**: `smtp`
- **호스트**: `smtp.daum.net`
- **포트**: `465`
- **암호화**: `ssl`
- **사용자명**: 카카오 메일 주소 (예: `your-email@hanmail.net`)
- **비밀번호**: 카카오 계정 비밀번호
- **발신자 이메일**: 카카오 메일 주소

**주의사항**:
- 카카오도 일일 발송량 제한이 있습니다
- 스팸 필터에 걸릴 수 있습니다

---

## 📝 설정 예시

### Mailgun 설정 예시
```
메일러: smtp
호스트: smtp.mailgun.org
포트: 587
암호화: tls
사용자명: postmaster@sandbox12345.mailgun.org
비밀번호: [Mailgun에서 생성한 비밀번호]
발신자 이메일: noreply@sandbox12345.mailgun.org
발신자 이름: SEOom Builder
```

### SendGrid 설정 예시
```
메일러: smtp
호스트: smtp.sendgrid.net
포트: 587
암호화: tls
사용자명: apikey
비밀번호: [SendGrid API 키]
발신자 이메일: noreply@seoomweb.com
발신자 이름: SEOom Builder
```

### 네이버 메일 설정 예시
```
메일러: smtp
호스트: smtp.naver.com
포트: 587
암호화: tls
사용자명: your-email@naver.com
비밀번호: [네이버 계정 비밀번호]
발신자 이메일: your-email@naver.com
발신자 이름: SEOom Builder
```

---

## ✅ 추천 순서

1. **초기 테스트**: **SendGrid** (무료 티어 영구 제공, 설정 간단)
2. **프로덕션**: **Mailgun** (신뢰성 높음, 통계 제공)
3. **비용 최소화**: **AWS SES** (AWS 사용 시)
4. **한국 서비스**: **네이버/카카오** (간단한 용도)

---

## 🔧 테스트 방법

설정 후 관리자 페이지에서:
1. 메일 설정 페이지 접속
2. "테스트 메일 발송" 기능 사용
3. 자신의 이메일로 테스트 메일 발송
4. 수신 확인

---

## ⚠️ 주의사항

1. **무료 티어 제한**: 각 서비스의 무료 티어 제한 확인
2. **스팸 필터**: 네이버/카카오는 스팸 필터에 걸릴 수 있음
3. **발신자 인증**: SendGrid는 발신자 이메일 인증 필수
4. **도메인 인증**: Mailgun은 프로덕션 사용 시 도메인 인증 권장

---

## 💡 추가 팁

- **발송량이 적은 경우**: SendGrid 무료 티어 추천
- **발송량이 많은 경우**: Mailgun 또는 AWS SES 추천
- **한국 사용자 대상**: 네이버/카카오도 고려 가능
- **비용 최소화**: AWS SES (발송량에 따라 매우 저렴)

