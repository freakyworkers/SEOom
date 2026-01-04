# PHP & Composer 설치 가이드 (Windows)

## 방법 1: Laragon 사용 (권장 - 가장 쉬움)

Laragon은 PHP, MySQL, Apache를 한 번에 설치해주는 통합 패키지입니다.

### 1-1. Laragon 다운로드 및 설치
1. https://laragon.org/download/ 접속
2. "Laragon Full" 다운로드 (PHP 8.1+ 포함)
3. 설치 프로그램 실행
4. 기본 설정으로 설치 진행

### 1-2. Composer 설치
Laragon에는 Composer가 포함되어 있지만, 별도로 설치하려면:
1. Laragon 실행
2. 메뉴 → Tools → Composer → Install/Update

### 1-3. 확인
```bash
php --version
composer --version
```

---

## 방법 2: XAMPP 사용

### 2-1. XAMPP 설치
1. https://www.apachefriends.org/download.html 접속
2. PHP 8.1+ 버전 다운로드
3. 설치 (Apache, MySQL, PHP 선택)

### 2-2. PHP 경로 추가
1. 시스템 환경 변수 편집
2. Path에 추가: `C:\xampp\php`
3. 명령 프롬프트 재시작

### 2-3. Composer 설치
1. https://getcomposer.org/download/ 접속
2. "Composer-Setup.exe" 다운로드
3. 설치 프로그램 실행
4. PHP 경로 자동 감지 (XAMPP의 PHP 선택)

---

## 방법 3: 수동 설치

### 3-1. PHP 설치
1. https://windows.php.net/download/ 접속
2. PHP 8.1+ Thread Safe 버전 다운로드
3. `C:\php` 폴더에 압축 해제
4. 환경 변수 Path에 `C:\php` 추가

### 3-2. Composer 설치
1. https://getcomposer.org/download/ 접속
2. "Composer-Setup.exe" 다운로드
3. 설치 프로그램 실행

---

## 설치 확인

설치가 완료되면 새 명령 프롬프트를 열고:

```bash
php --version
composer --version
```

정상적으로 버전이 표시되면 설치 완료입니다!

---

## 빠른 설치 스크립트

아래 스크립트를 실행하면 자동으로 확인하고 안내해드립니다.












