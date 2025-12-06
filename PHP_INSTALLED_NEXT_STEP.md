# PHP 설치 완료! ✅

**상태:** PHP 8.3.28 설치 완료!

---

## ✅ 확인된 정보

- ✅ **PHP 버전**: 8.3.28
- ✅ **설치 상태**: 정상
- ✅ **Zend OPcache**: 활성화됨

---

## 🎯 다음 단계: Composer 설치 (6-4 단계)

Composer는 PHP의 패키지 관리자예요. Laravel 프로젝트에 필요해요!

### 1단계: 홈 디렉토리로 이동

```bash
cd ~
```

Enter 키 누르기

### 2단계: Composer 다운로드

```bash
curl -sS https://getcomposer.org/installer | php
```

Enter 키 누르고 완료될 때까지 기다리세요.  
**예상 시간:** 30초~1분

### 3단계: Composer 전역 설치

```bash
sudo mv composer.phar /usr/local/bin/composer
```

Enter 키 누르기

### 4단계: Composer 실행 권한 부여

```bash
sudo chmod +x /usr/local/bin/composer
```

Enter 키 누르기

### 5단계: Composer 버전 확인

```bash
composer --version
```

Enter 키 누르면 Composer 버전이 표시돼요.

**예상 결과:**
```
Composer version 2.x.x ...
```

---

## 📋 전체 명령어 (복사해서 사용)

```bash
# 홈 디렉토리로 이동
cd ~

# Composer 다운로드
curl -sS https://getcomposer.org/installer | php

# Composer 전역 설치
sudo mv composer.phar /usr/local/bin/composer

# 실행 권한 부여
sudo chmod +x /usr/local/bin/composer

# 버전 확인
composer --version
```

---

## 💡 팁

- 명령어는 **하나씩** 입력하세요
- 각 명령어가 완료될 때까지 기다리세요
- `curl` 명령어는 인터넷에서 Composer를 다운로드해요

---

## ✅ 완료 확인

`composer --version` 명령어를 실행했을 때:
- Composer 버전이 나오면 성공! ✅
- 에러가 나오면 알려주세요

---

## 🎯 다음 단계 (Composer 설치 완료 후)

1. **MySQL 설치** (6-5 단계)
2. **Apache 설치** (6-7 단계)
3. **Git 설치** (6-8 단계)
4. **프로젝트 업로드** (6-9 단계)
5. **Laravel 설정** (6-10 단계)

---

**Composer 설치 명령어를 실행하세요!**

