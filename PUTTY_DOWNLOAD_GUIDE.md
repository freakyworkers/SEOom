# PuTTY 다운로드 가이드 (올바른 사이트)

**⚠️ 중요:** 올바른 사이트에서 다운로드하세요!

---

## ✅ 올바른 PuTTY 다운로드 사이트

### 공식 사이트 (권장)

1. **공식 다운로드 페이지**
   - URL: https://www.chiark.greenend.org.uk/~sgtatham/putty/latest.html
   - 이게 **진짜 공식 사이트**예요!

2. **GitHub 릴리스 페이지** (대안)
   - URL: https://github.com/putty/putty/releases/latest
   - 공식 GitHub 저장소예요!

### 다운로드할 파일

**Windows 64-bit:**
- `putty-64bit-X.XX-installer.msi` (설치 파일)
- 또는 `putty-64bit-X.XX-installer.exe`

**버전:**
- 최신 버전을 다운로드하세요 (보통 0.78 이상)

---

## ❌ 피해야 할 사이트

### putty.org는 공식 사이트가 아닙니다!

- ❌ `putty.org` - 공식 사이트가 아니에요!
- ❌ `putty.com` - 공식 사이트가 아니에요!
- ⚠️ 이런 사이트는 광고나 다른 내용이 나올 수 있어요

---

## 📋 다운로드 방법

### 방법 1: 공식 사이트에서 다운로드

1. 브라우저에서 접속: https://www.chiark.greenend.org.uk/~sgtatham/putty/latest.html
2. **"64-bit x86"** 또는 **"64-bit installer"** 찾기
3. **"msi"** 또는 **"exe"** 파일 다운로드
4. 다운로드한 파일 실행하여 설치

### 방법 2: GitHub에서 다운로드

1. 브라우저에서 접속: https://github.com/putty/putty/releases/latest
2. **"Assets"** 섹션 펼치기
3. **"putty-64bit-X.XX-installer.msi"** 다운로드
4. 다운로드한 파일 실행하여 설치

---

## 🔍 올바른 사이트 확인 방법

### 공식 사이트 특징:

1. **URL 확인**
   - ✅ `chiark.greenend.org.uk` - 공식 사이트
   - ✅ `github.com/putty/putty` - 공식 GitHub

2. **페이지 내용 확인**
   - ✅ PuTTY 다운로드 링크가 바로 보여요
   - ✅ 버전 정보가 명확해요
   - ✅ 설치 파일이 바로 다운로드 가능해요

3. **피해야 할 사이트 특징**
   - ❌ 이상한 내용이 나와요 (인터뷰, 광고 등)
   - ❌ PuTTY 다운로드 링크가 불명확해요
   - ❌ 다른 소프트웨어를 추천해요

---

## 💡 대안: Windows Terminal 사용 (더 쉬움!)

PuTTY 대신 **Windows Terminal**을 사용할 수도 있어요!

**장점:**
- Windows 10/11에 기본 설치되어 있어요
- 추가 다운로드 불필요
- 더 현대적이고 사용하기 쉬워요

**사용 방법:**
1. Windows Terminal 실행
2. PowerShell 탭 선택
3. 다음 명령어 입력:

```powershell
# 키 파일 권한 설정
icacls "C:\Users\사용자이름\Downloads\seoom-key.pem" /inheritance:r
icacls "C:\Users\사용자이름\Downloads\seoom-key.pem" /grant:r "%USERNAME%:R"

# 서버 접속
ssh -i "C:\Users\사용자이름\Downloads\seoom-key.pem" ubuntu@퍼블릭IP주소
```

---

## 📝 요약

| 방법 | 장점 | 단점 |
|------|------|------|
| **PuTTY** | 널리 사용됨, GUI 제공 | 다운로드 필요 |
| **Windows Terminal** | 기본 설치됨, 추가 다운로드 불필요 | 명령어 사용 필요 |

**추천:**
- Windows Terminal이 더 쉬워요! (추가 다운로드 불필요)
- PuTTY를 원하면 공식 사이트에서만 다운로드하세요!

---

## 🆘 문제 해결

### 문제: putty.org에서 이상한 페이지가 나와요

**해결 방법:**
- 그 사이트는 공식 사이트가 아니에요!
- 위에 적힌 공식 사이트로 가세요
- 또는 Windows Terminal을 사용하세요

### 문제: 다운로드가 안 돼요

**해결 방법:**
- Windows Terminal 사용 (추천!)
- 또는 GitHub 릴리스 페이지에서 다운로드

---

**마지막 업데이트:** 2025년 1월

