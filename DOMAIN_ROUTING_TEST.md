# 도메인 라우팅 테스트 가이드

## 테스트 준비

### 1. hosts 파일 수정 (Windows)

1. 관리자 권한으로 메모장 실행
2. `C:\Windows\System32\drivers\etc\hosts` 파일 열기
3. 다음 내용 추가:

```
127.0.0.1 seoom.local
127.0.0.1 www.seoom.local
127.0.0.1 test-site.seoom.local
127.0.0.1 example.local
127.0.0.1 www.example.local
```

### 2. .env 파일 설정

`.env` 파일에 다음 추가:
```
MASTER_DOMAIN=seoom.local
APP_URL=http://seoom.local:8000
```

### 3. 서버 실행

```bash
php artisan serve --host=0.0.0.0 --port=8000
```

### 4. 브라우저 테스트

1. **마스터 도메인**: http://seoom.local:8000
2. **서브도메인**: http://test-site.seoom.local:8000
3. **커스텀 도메인**: http://example.local:8000

## 테스트 시나리오

### 시나리오 1: 마스터 도메인 접근
- URL: http://seoom.local:8000
- 예상: 마스터 사이트 홈페이지 표시

### 시나리오 2: 서브도메인 접근
- URL: http://test-site.seoom.local:8000
- 예상: slug가 "test-site"인 사이트 홈페이지 표시

### 시나리오 3: 커스텀 도메인 접근
- URL: http://example.local:8000
- 예상: domain이 "example.local"인 사이트 홈페이지 표시

### 시나리오 4: 슬러그 기반 접근 (하위 호환)
- URL: http://localhost:8000/site/test-site
- 예상: 기존 방식대로 작동

