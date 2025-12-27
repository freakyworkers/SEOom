# 회원가입 추가 기능 구현 플랜

## 📋 구현 순서 및 우선순위

### ✅ 1단계: 가입 포인트 기능 (우선 구현)
**난이도:** ⭐ (쉬움)  
**예상 소요 시간:** 1-2시간  
**의존성:** 없음 (독립적)

#### 작업 내용
1. **회원가입 설정 페이지 수정**
   - 기본 폼 하단에 "가입 포인트" 입력 필드 추가
   - 숫자 입력 필드 (기본값: 0)

2. **데이터베이스**
   - SiteSetting에 `registration_signup_points` 저장

3. **회원가입 로직 수정**
   - `AuthService::register()` 메서드에서 가입 포인트 지급
   - `User::addPoints()` 메서드 활용

4. **알림 기능 (선택)**
   - 가입 포인트 지급 알림 생성

#### 파일 수정
- `resources/views/admin/registration-settings.blade.php`
- `app/Http/Controllers/AdminController.php` (updateRegistrationSettings)
- `app/Services/AuthService.php` (register 메서드)
- `app/Services/NotificationService.php` (선택)

---

### ✅ 2단계: 추천인 기능 (2단계와 연계)
**난이도:** ⭐⭐ (보통)  
**예상 소요 시간:** 3-4시간  
**의존성:** 가입 포인트 기능 (1단계)

#### 작업 내용
1. **데이터베이스 마이그레이션**
   - `users` 테이블에 `referrer_id` 컬럼 추가 (nullable, foreign key)
   - 추천인 관계 테이블 또는 컬럼

2. **회원가입 설정 페이지 수정**
   - 추천인 기능 활성화 체크박스
   - 추천인 지급 포인트 입력 필드
   - 가입자 지급 포인트 입력 필드

3. **회원가입 폼 수정**
   - 추천인 닉네임 입력 필드 추가 (설정 활성화 시)
   - 추천인 검증 (닉네임으로 기존 회원 찾기)
   - AJAX로 실시간 추천인 확인

4. **회원가입 로직 수정**
   - 추천인 ID 저장
   - 추천인에게 포인트 지급
   - 가입자에게 포인트 지급
   - 알림 생성 (추천인, 가입자 모두)

5. **사용자 상세보기 페이지**
   - 추천인 정보 표시
   - 추천한 회원 목록 표시 (선택)

#### 파일 수정/생성
- `database/migrations/xxxx_add_referrer_to_users_table.php` (새로 생성)
- `resources/views/admin/registration-settings.blade.php`
- `resources/views/auth/register.blade.php`
- `app/Http/Controllers/AdminController.php`
- `app/Http/Controllers/AuthController.php`
- `app/Services/AuthService.php`
- `app/Models/User.php` (referrer 관계 추가)
- `resources/views/admin/user-detail.blade.php`

#### 필요한 기능
- 추천인 닉네임 검증 API
- 포인트 지급 로직
- 알림 생성

---

### ✅ 3단계: 이메일 인증 기능
**난이도:** ⭐⭐ (보통)  
**예상 소요 시간:** 4-5시간  
**의존성:** 없음 (독립적)

#### 작업 내용
1. **데이터베이스 마이그레이션**
   - `email_verifications` 테이블 생성
     - `id`, `user_id`, `email`, `token`, `expires_at`, `verified_at`, `created_at`, `updated_at`

2. **회원가입 설정 페이지**
   - 이메일 인증 활성화 체크박스 활성화

3. **회원가입 폼 수정**
   - 이메일 입력칸 옆에 "인증하기" 버튼 추가
   - 인증 코드 입력 필드 (또는 링크 클릭 방식)
   - 인증 상태 표시 (인증 완료/미인증)

4. **이메일 인증 로직**
   - 인증 토큰 생성 및 저장
   - 이메일 발송 (Laravel Mail 사용)
   - 인증 링크 클릭 시 검증
   - 인증 완료 처리

5. **회원가입 로직 수정**
   - 이메일 인증 필수 여부 확인
   - 인증되지 않은 경우 가입 제한

#### 파일 수정/생성
- `database/migrations/xxxx_create_email_verifications_table.php` (새로 생성)
- `app/Models/EmailVerification.php` (새로 생성)
- `resources/views/auth/register.blade.php`
- `app/Http/Controllers/AuthController.php` (sendVerificationEmail, verifyEmail 메서드)
- `app/Mail/EmailVerificationMail.php` (새로 생성)
- `resources/views/emails/verification.blade.php` (새로 생성)

#### 필요한 설정
- `.env` 파일에 메일 서버 설정
  ```env
  MAIL_MAILER=smtp
  MAIL_HOST=smtp.gmail.com
  MAIL_PORT=587
  MAIL_USERNAME=your_email@gmail.com
  MAIL_PASSWORD=your_app_password
  MAIL_ENCRYPTION=tls
  MAIL_FROM_ADDRESS=noreply@yourdomain.com
  MAIL_FROM_NAME="${APP_NAME}"
  ```

#### 참고
- Gmail 사용 시: 앱 비밀번호 생성 필요
- 다른 메일 서비스: SendGrid, Mailgun, AWS SES 등

---

### ⏳ 4단계: 전화번호 인증 기능
**난이도:** ⭐⭐⭐ (어려움)  
**예상 소요 시간:** 6-8시간  
**의존성:** 없음 (독립적)

#### 작업 내용
1. **데이터베이스 마이그레이션**
   - `phone_verifications` 테이블 생성
     - `id`, `phone`, `code`, `expires_at`, `verified_at`, `attempts`, `created_at`, `updated_at`

2. **회원가입 설정 페이지**
   - 전화번호 인증 활성화 체크박스 활성화

3. **회원가입 폼 수정**
   - 전화번호 입력칸 옆에 "인증번호 발송" 버튼
   - 인증번호 입력 필드
   - 인증하기 버튼
   - 재발송 기능 (1분 제한)

4. **SMS 발송 로직**
   - 인증번호 생성 (6자리 숫자)
   - SMS API 연동
   - 인증번호 저장 및 만료 시간 설정
   - 재발송 제한 (1분 간격)

5. **인증 검증 로직**
   - 인증번호 확인
   - 만료 시간 확인
   - 시도 횟수 제한 (5회)
   - 인증 완료 처리

#### 파일 수정/생성
- `database/migrations/xxxx_create_phone_verifications_table.php` (새로 생성)
- `app/Models/PhoneVerification.php` (새로 생성)
- `resources/views/auth/register.blade.php`
- `app/Http/Controllers/AuthController.php` (sendPhoneVerification, verifyPhone 메서드)
- `app/Services/SmsService.php` (새로 생성)

#### 필요한 설정
- SMS API 서비스 선택 및 가입
  - 옵션 1: 알리고 (Aligo) - 국내 서비스
  - 옵션 2: 카카오 알림톡
  - 옵션 3: AWS SNS
  - 옵션 4: Twilio (해외)

#### `.env` 설정 예시 (알리고 기준)
```env
SMS_PROVIDER=aligo
ALIGO_API_KEY=your_api_key
ALIGO_USER_ID=your_user_id
ALIGO_SENDER=01012345678
```

---

### ⏳ 5단계: 본인인증 기능
**난이도:** ⭐⭐⭐⭐ (매우 어려움)  
**예상 소요 시간:** 10-15시간  
**의존성:** 없음 (독립적)

#### 작업 내용
1. **본인인증 서비스 선택**
   - 옵션 1: 나이스 본인인증 (Nice 본인인증)
   - 옵션 2: KG이니시스 본인인증
   - 옵션 3: 아임포트 본인인증
   - 옵션 4: 휴대폰 본인인증 (통신사 API)

2. **데이터베이스 마이그레이션**
   - `identity_verifications` 테이블 생성
     - `id`, `user_id`, `name`, `birth_date`, `gender`, `ci`, `di`, `phone`, `verified_at`, `created_at`, `updated_at`

3. **회원가입 설정 페이지**
   - 본인인증 활성화 체크박스 활성화

4. **회원가입 폼 수정**
   - 본인인증 버튼 추가
   - 본인인증 팝업 연동
   - 인증 완료 후 정보 자동 입력

5. **본인인증 API 연동**
   - 본인인증 서비스 API 연동
   - 인증 결과 처리
   - 개인정보 암호화 저장

#### 파일 수정/생성
- `database/migrations/xxxx_create_identity_verifications_table.php` (새로 생성)
- `app/Models/IdentityVerification.php` (새로 생성)
- `resources/views/auth/register.blade.php`
- `app/Http/Controllers/AuthController.php` (verifyIdentity 메서드)
- `app/Services/IdentityVerificationService.php` (새로 생성)

#### 필요한 설정
- 본인인증 서비스 계약 및 API 키 발급
- 개인정보보호법 준수
- 암호화 저장

---

## 📊 전체 일정 예상

| 단계 | 기능 | 난이도 | 예상 시간 | 우선순위 |
|------|------|--------|-----------|----------|
| 1 | 가입 포인트 | ⭐ | 1-2시간 | 🔥 최우선 |
| 2 | 추천인 기능 | ⭐⭐ | 3-4시간 | 🔥 최우선 |
| 3 | 이메일 인증 | ⭐⭐ | 4-5시간 | ⚡ 높음 |
| 4 | 전화번호 인증 | ⭐⭐⭐ | 6-8시간 | ⚠️ 중간 |
| 5 | 본인인증 | ⭐⭐⭐⭐ | 10-15시간 | ⚠️ 낮음 |

**총 예상 시간:** 24-34시간

---

## 🎯 권장 구현 순서

### Phase 1: 기본 기능 (1-2주)
1. ✅ 가입 포인트 기능
2. ✅ 추천인 기능

### Phase 2: 인증 기능 (2-3주)
3. ✅ 이메일 인증
4. ⏳ 전화번호 인증

### Phase 3: 고급 기능 (3-4주)
5. ⏳ 본인인증

---

## 📝 각 단계별 체크리스트

### 1단계: 가입 포인트
- [ ] 회원가입 설정 페이지에 가입 포인트 입력 필드 추가
- [ ] 설정 저장 로직 구현
- [ ] 회원가입 시 포인트 지급 로직 구현
- [ ] 포인트 지급 알림 생성 (선택)
- [ ] 테스트

### 2단계: 추천인 기능
- [ ] 데이터베이스 마이그레이션 (referrer_id)
- [ ] 회원가입 설정 페이지 수정
- [ ] 추천인 지급 포인트, 가입자 지급 포인트 입력 필드
- [ ] 회원가입 폼에 추천인 입력 필드 추가
- [ ] 추천인 검증 API
- [ ] 회원가입 시 추천인 포인트 지급 로직
- [ ] 사용자 상세보기 페이지에 추천인 정보 표시
- [ ] 테스트

### 3단계: 이메일 인증
- [ ] 데이터베이스 마이그레이션
- [ ] EmailVerification 모델 생성
- [ ] 이메일 인증 활성화
- [ ] 회원가입 폼에 인증 버튼 추가
- [ ] 이메일 발송 로직
- [ ] 인증 링크 검증 로직
- [ ] 메일 템플릿 작성
- [ ] 메일 서버 설정 가이드
- [ ] 테스트

### 4단계: 전화번호 인증
- [ ] 데이터베이스 마이그레이션
- [ ] PhoneVerification 모델 생성
- [ ] SMS 서비스 선택 및 계약
- [ ] SMS 발송 로직 구현
- [ ] 인증번호 검증 로직
- [ ] 재발송 제한 로직
- [ ] 회원가입 폼 수정
- [ ] 테스트

### 5단계: 본인인증
- [ ] 본인인증 서비스 선택 및 계약
- [ ] 데이터베이스 마이그레이션
- [ ] IdentityVerification 모델 생성
- [ ] 본인인증 API 연동
- [ ] 회원가입 폼에 본인인증 버튼 추가
- [ ] 개인정보 암호화 저장
- [ ] 테스트

---

## 💡 구현 팁

1. **가입 포인트와 추천인 기능은 함께 구현하는 것이 좋습니다.**
   - 두 기능 모두 포인트 지급과 관련이 있어서 로직이 유사합니다.

2. **이메일 인증은 먼저 구현하는 것을 권장합니다.**
   - 전화번호 인증보다 구현이 간단하고, 외부 API 의존성이 적습니다.

3. **전화번호 인증과 본인인증은 외부 서비스 계약이 필요합니다.**
   - 개발 전에 서비스 선택 및 계약을 완료하는 것이 좋습니다.

4. **각 기능은 독립적으로 테스트 가능하도록 구현합니다.**
   - 설정으로 활성화/비활성화가 가능해야 합니다.

---

## 🔧 기술 스택

- **백엔드:** Laravel 10+
- **데이터베이스:** MySQL
- **이메일:** Laravel Mail (SMTP)
- **SMS:** 알리고, 카카오 알림톡, AWS SNS 등
- **본인인증:** 나이스 본인인증, KG이니시스 등

---

## 📌 다음 단계

1단계(가입 포인트)부터 시작하여 순차적으로 구현하는 것을 권장합니다.






