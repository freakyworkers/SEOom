# 서버 접속 명령어 (실제 경로 사용)

**키 파일 경로:** `C:\Users\kangd\Desktop\세움배포파일\seoom-key.pem`

---

## 📋 PowerShell에서 실행할 명령어

### 1단계: 키 파일 권한 설정 (한 번만 실행)

PowerShell에 다음 명령어를 **복사해서 붙여넣기**하세요:

```powershell
icacls "C:\Users\kangd\Desktop\세움배포파일\seoom-key.pem" /inheritance:r
icacls "C:\Users\kangd\Desktop\세움배포파일\seoom-key.pem" /grant:r "%USERNAME%:R"
```

**실행 방법:**
1. Windows Terminal 또는 PowerShell 열기
2. 위 명령어를 복사
3. PowerShell 창에 붙여넣기 (마우스 우클릭 또는 `Ctrl + V`)
4. Enter 키 누르기
5. 두 번째 명령어도 같은 방식으로 실행

**결과:**
- 성공하면 아무 메시지도 안 나올 수 있어요 (정상!)
- 에러가 나오면 알려주세요

---

### 2단계: 서버 접속

**서버 접속 명령어:**

```powershell
ssh -i "C:\Users\kangd\Desktop\세움배포파일\seoom-key.pem" ubuntu@52.79.104.130
```

**실행 방법:**
1. 위 명령어를 복사
2. `퍼블릭IP주소` 부분을 실제 IP 주소로 변경
   - 예: `54.123.45.67` → `ubuntu@54.123.45.67`로 변경
3. PowerShell에 붙여넣기
4. Enter 키 누르기

**첫 접속 시:**
- `Are you sure you want to continue connecting (yes/no)?` 메시지가 나와요
- `yes` 입력하고 Enter

**성공하면:**
- `ubuntu@ip-xxx-xxx-xxx-xxx:~$` 같은 프롬프트가 나와요
- 이제 서버에 접속된 거예요! 🎉

---

## 🔍 서버 정보

- **AWS EC2 퍼블릭 IPv4 주소:** `52.79.104.130`
- **키 파일 경로:** `C:\Users\kangd\Desktop\세움배포파일\seoom-key.pem`
- **마스터 사이트:** `seoomweb.com`
- **마스터 계정:** `master@seoom.com` / `Qkqh090909!`

---

## 📝 전체 명령어 (복사해서 사용)

### 퍼블릭 IP 주소를 확인한 후:

```powershell
# 1단계: 키 파일 권한 설정 (한 번만 실행)
icacls "C:\Users\kangd\Desktop\세움배포파일\seoom-key.pem" /inheritance:r
icacls "C:\Users\kangd\Desktop\세움배포파일\seoom-key.pem" /grant:r "%USERNAME%:R"

# 2단계: 서버 접속
ssh -i "C:\Users\kangd\Desktop\세움배포파일\seoom-key.pem" ubuntu@52.79.104.130
```

---

## 🆘 문제 해결

### 문제: "Permission denied" 오류

**해결 방법:**
- 키 파일 권한 설정 명령어를 다시 실행하세요
- 관리자 권한으로 PowerShell 실행해보세요

### 문제: "No such file or directory" 오류

**해결 방법:**
- 키 파일 경로가 정확한지 확인하세요
- 파일 이름에 오타가 없는지 확인하세요
- 경로에 한글이 있어도 괜찮아요!

### 문제: "Connection timed out" 오류

**해결 방법:**
- 퍼블릭 IP 주소가 맞는지 확인하세요
- 인스턴스 상태가 "실행 중"인지 확인하세요
- 보안 그룹에서 SSH(포트 22)가 열려있는지 확인하세요

---

## 💡 팁

### 명령어 복사 방법

1. 위의 명령어를 마우스로 드래그해서 선택
2. `Ctrl + C`로 복사
3. PowerShell 창에 `Ctrl + V`로 붙여넣기
   - 또는 마우스 우클릭

### 경로에 한글이 있어도 괜찮아요!

- `세움배포파일` 같은 한글 폴더 이름도 문제없어요
- 따옴표(`"`)로 감싸면 됩니다

---

**다음 단계:** 퍼블릭 IP 주소를 확인한 후 위 명령어를 실행하세요!

