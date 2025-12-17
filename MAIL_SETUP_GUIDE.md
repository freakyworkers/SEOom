# 이메일 발송 설정 가이드

## .env 파일에 추가할 메일 설정

### Gmail SMTP 사용 (권장)

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@gmail.com
MAIL_FROM_NAME="${APP_NAME}"
```

### Gmail 앱 비밀번호 생성 방법

1. **Google 계정 보안 설정 접속**
   - https://myaccount.google.com/security 접속
   - 또는 Google 계정 → 보안

2. **2단계 인증 활성화** (필수)
   - "2단계 인증" 클릭
   - 활성화 안 되어 있으면 활성화

3. **앱 비밀번호 생성**
   - "앱 비밀번호" 클릭
   - "앱 선택" → "메일" 선택
   - "기기 선택" → "기타(맞춤 이름)" 선택
   - 이름 입력 (예: "SEOom Builder")
   - "생성" 클릭

4. **생성된 16자리 비밀번호 복사**
   - 예: `abcd efgh ijkl mnop` (공백 제거하여 사용)
   - `.env` 파일의 `MAIL_PASSWORD`에 입력

### 네이버 메일 사용

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.naver.com
MAIL_PORT=587
MAIL_USERNAME=your-email@naver.com
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@naver.com
MAIL_FROM_NAME="${APP_NAME}"
```

### 카카오 메일 사용

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.daum.net
MAIL_PORT=465
MAIL_USERNAME=your-email@hanmail.net
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS=your-email@hanmail.net
MAIL_FROM_NAME="${APP_NAME}"
```

## 설정 후 확인

1. `.env` 파일 저장
2. 캐시 클리어 (선택사항)
   ```bash
   php artisan config:clear
   ```
3. 회원가입 페이지에서 이메일 인증 테스트

## 테스트 방법

1. 회원가입 페이지 접속
2. 이메일 입력 (예: kangdoner@gmail.com)
3. "인증하기" 버튼 클릭
4. 이메일 수신 확인
5. 인증번호 입력하여 검증

## 문제 해결

### "Connection could not be established" 오류
- 방화벽에서 포트 587 또는 465 허용 확인
- Gmail의 경우 "보안 수준이 낮은 앱의 액세스" 허용 (비권장)

### "Authentication failed" 오류
- Gmail: 앱 비밀번호가 올바른지 확인
- 네이버/카카오: 일반 비밀번호 사용 가능

### 이메일이 스팸함에 들어가는 경우
- SPF, DKIM, DMARC 레코드 설정 (도메인 사용 시)
- 발신자 주소를 신뢰할 수 있는 주소로 설정





