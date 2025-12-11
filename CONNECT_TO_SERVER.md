# 서버 접속 명령어 (완성)

**퍼블릭 IP 주소:** `52.79.104.130`  
**키 파일 경로:** `C:\Users\kangd\Desktop\세움배포파일\seoom-key.pem`

---

## 🎯 서버 접속 명령어

PowerShell에 다음 명령어를 **복사해서 붙여넣기**하세요:

```powershell
ssh -i "C:\Users\kangd\Desktop\세움배포파일\seoom-key.pem" ubuntu@52.79.104.130
```

---

## 📋 실행 방법

1. **Windows Terminal 또는 PowerShell 열기**
2. **위 명령어를 복사**
3. **PowerShell 창에 붙여넣기** (마우스 우클릭 또는 `Ctrl + V`)
4. **Enter 키 누르기**

---

## ✅ 예상 결과

### 첫 접속 시:

```
The authenticity of host '52.79.104.130 (52.79.104.130)' can't be established.
ED25519 key fingerprint is SHA256:...
Are you sure you want to continue connecting (yes/no/[fingerprint])?
```

**→ `yes` 입력하고 Enter**

### 접속 성공 시:

```
Welcome to Ubuntu 22.04.3 LTS (GNU/Linux ...)

...

ubuntu@ip-xxx-xxx-xxx-xxx:~$
```

**이제 서버에 접속된 거예요!** 🎉

---

## 🆘 문제 해결

### 문제: "Permission denied" 오류

**해결 방법:**
- 키 파일 경로가 정확한지 확인
- 키 파일 권한이 제대로 설정되었는지 확인

### 문제: "Connection timed out" 오류

**해결 방법:**
- 인스턴스 상태가 "실행 중"인지 확인
- 보안 그룹에서 SSH(포트 22)가 열려있는지 확인
- 퍼블릭 IP 주소가 맞는지 확인

### 문제: "Host key verification failed" 오류

**해결 방법:**
- `yes`를 입력했는지 확인
- 또는 다음 명령어로 호스트 키 제거 후 재시도:
  ```powershell
  ssh-keygen -R 52.79.104.130
  ```

---

## 📝 다음 단계

서버 접속이 성공하면:

1. **서버 업데이트** (6-2 단계)
2. **PHP 설치** (6-3 단계)
3. **Composer 설치** (6-4 단계)
4. **MySQL 설치** (6-5 단계)
5. **기타 프로그램 설치** 계속 진행

---

**명령어를 실행해보시고 결과를 알려주세요!**

