# Git 설치 완료! ✅

**상태:** Git 2.34.1 설치 완료!

---

## ✅ 완료된 작업

- ✅ Git 설치 완료
- ✅ Git 버전: 2.34.1
- ✅ 다음 단계 준비 완료

---

## 🎯 다음 단계: 프로젝트 업로드 (6-9 단계)

프로젝트 파일을 서버에 업로드해야 해요!

### 방법 선택

**방법 1: Git 사용 (권장)**
- 프로젝트가 GitHub 등에 올려져 있는 경우
- 가장 간단하고 빠른 방법

**방법 2: WinSCP 사용**
- 로컬 컴퓨터에 있는 프로젝트 파일을 직접 업로드
- Git 저장소가 없는 경우

---

## 📋 방법 1: Git 사용 (프로젝트가 GitHub에 있는 경우)

### 1단계: 웹 루트로 이동

```bash
cd /var/www
```

Enter 키 누르기

### 2단계: 프로젝트 클론

```bash
sudo git clone https://github.com/사용자이름/저장소이름.git seoom
```

Enter 키 누르기

**⚠️ 중요:** 
- `사용자이름`과 `저장소이름`을 실제 값으로 변경하세요
- 예: `sudo git clone https://github.com/kangd/seoom-builder.git seoom`

### 3단계: 소유권 변경

```bash
sudo chown -R www-data:www-data /var/www/seoom
sudo chmod -R 755 /var/www/seoom
```

Enter 키 누르기

---

## 📋 방법 2: WinSCP 사용 (로컬 파일 업로드)

### 1단계: WinSCP 다운로드 및 설치

1. https://winscp.net/ 접속
2. 다운로드 및 설치

### 2단계: 서버 연결

1. WinSCP 실행
2. **"호스트 이름"**: `54.180.2.108` (퍼블릭 IP 주소)
3. **"사용자 이름"**: `ubuntu`
4. **"고급"** → **"인증"** → **"개인 키 파일"**: `.ppk` 파일 선택
   - `.pem` 파일이 있다면 PuTTYgen으로 `.ppk`로 변환 필요
5. **"로그인"** 클릭

### 3단계: 파일 업로드

1. 왼쪽: 로컬 컴퓨터 폴더 (프로젝트 폴더)
2. 오른쪽: 서버 (`/var/www` 폴더)
3. 프로젝트 폴더를 드래그 앤 드롭
4. 업로드 완료 대기

### 4단계: 소유권 변경 (서버에서)

서버 터미널에서:

```bash
sudo chown -R www-data:www-data /var/www/seoom
sudo chmod -R 755 /var/www/seoom
```

---

## 💡 어떤 방법을 선택해야 하나요?

- **GitHub에 프로젝트가 있나요?** → 방법 1 (Git 사용)
- **로컬에만 있나요?** → 방법 2 (WinSCP 사용)

---

## 🎯 다음 단계 (프로젝트 업로드 완료 후)

1. **Laravel 설정** (6-10 단계)
2. **Apache 가상 호스트 설정** (6-11 단계)

---

**어떤 방법으로 진행할까요? GitHub에 프로젝트가 있나요, 아니면 로컬에만 있나요?**

