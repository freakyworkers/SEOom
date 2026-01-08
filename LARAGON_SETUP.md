# Laragon 설정 가이드

## ✅ Laragon 설치 완료 확인

Laragon이 설치되어 있습니다! 이제 PHP와 Composer를 사용할 수 있도록 설정해야 합니다.

---

## 방법 1: Laragon 터미널 사용 (가장 쉬움! ⭐)

**Laragon의 자체 터미널을 사용하면 자동으로 PHP와 Composer가 인식됩니다!**

1. **Laragon 실행**
2. **"터미널" 버튼 클릭** (하단에 있는 터미널 아이콘)
3. 터미널이 열리면 자동으로 PHP와 Composer 경로가 설정됨
4. 다음 명령어로 확인:
   ```bash
   php --version
   composer --version
   ```

**이 방법을 사용하면 환경 변수 설정이 필요 없습니다!**

---

## 방법 2: 환경 변수 자동 설정

### 2-1. 관리자 권한으로 PowerShell 실행
1. Windows 검색에서 "PowerShell" 검색
2. "관리자 권한으로 실행" 클릭

### 2-2. 스크립트 실행
```powershell
cd C:\Users\kangd\Desktop\01.seoom\SEOom
.\setup-laragon-env.ps1
```

### 2-3. 명령 프롬프트 재시작
- 현재 PowerShell 창 닫기
- 새 PowerShell 창 열기
- `php --version` 확인

---

## 방법 3: 수동 환경 변수 설정

1. **시스템 환경 변수 편집**
   - Windows 검색 → "환경 변수" → "시스템 환경 변수 편집"
   - "환경 변수" 버튼 클릭

2. **Path 변수 편집**
   - "시스템 변수"에서 "Path" 선택 → "편집"
   - "새로 만들기" 클릭
   - 다음 경로 추가:
     ```
     C:\laragon\bin\php\php-8.3.26-Win32-vs16-x64
     C:\laragon\bin\composer
     ```

3. **확인**
   - 모든 창 닫기
   - 새 명령 프롬프트 열기
   - `php --version` 확인

---

## Composer 설치 확인

Laragon에 Composer가 포함되어 있지 않을 수 있습니다.

### Composer 설치 방법:

1. **Laragon에서 설치 (권장)**
   - Laragon 실행
   - 메뉴 → Tools → Composer → Install/Update

2. **또는 수동 설치**
   - https://getcomposer.org/download/
   - Composer-Setup.exe 다운로드
   - 설치 시 Laragon의 PHP 경로 선택

---

## 다음 단계

PHP와 Composer가 인식되면:

```bash
# 1. 프로젝트 디렉토리로 이동
cd C:\Users\kangd\Desktop\01.seoom\SEOom

# 2. Composer 의존성 설치
composer install

# 3. 환경 설정
copy .env.example .env
php artisan key:generate
```

---

## 문제 해결

### PHP가 인식되지 않을 때
- **Laragon 터미널 사용** (가장 쉬운 방법!)
- 또는 환경 변수 설정 후 명령 프롬프트 재시작

### Composer가 인식되지 않을 때
- Laragon → 메뉴 → Tools → Composer → Install/Update
- 또는 수동 설치: https://getcomposer.org/download/

### Laragon 경로가 다른 경우
- Laragon 설치 경로 확인
- `setup-laragon-env.ps1` 스크립트의 경로 수정

---

## 💡 추천 방법

**Laragon의 '터미널' 버튼을 사용하는 것이 가장 간단합니다!**
- 환경 변수 설정 불필요
- 자동으로 PHP/Composer 인식
- 별도 설정 없이 바로 사용 가능












