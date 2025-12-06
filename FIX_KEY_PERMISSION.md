# 키 파일 권한 설정 오류 해결

**문제:** `%USERNAME%`이 PowerShell에서 제대로 작동하지 않아요

---

## ✅ 해결 방법

PowerShell에서는 `%USERNAME%` 대신 실제 사용자 이름을 사용하거나 `$env:USERNAME`을 사용해야 해요.

### 방법 1: 실제 사용자 이름 사용 (권장)

사용자 이름이 `kangd`인 경우:

```powershell
icacls "C:\Users\kangd\Desktop\세움배포파일\seoom-key.pem" /grant:r "kangd:R"
```

### 방법 2: PowerShell 변수 사용

```powershell
icacls "C:\Users\kangd\Desktop\세움배포파일\seoom-key.pem" /grant:r "$env:USERNAME:R"
```

---

## 📋 실행할 명령어

**PowerShell에 다음 명령어를 입력하세요:**

```powershell
icacls "C:\Users\kangd\Desktop\세움배포파일\seoom-key.pem" /grant:r "kangd:R"
```

**또는:**

```powershell
icacls "C:\Users\kangd\Desktop\세움배포파일\seoom-key.pem" /grant:r "$env:USERNAME:R"
```

---

## ✅ 확인

명령어 실행 후:
- 성공하면 아무 메시지도 안 나올 수 있어요 (정상!)
- 또는 "처리된 파일" 메시지가 나와요

---

**다음 단계:** 위 명령어를 실행한 후, 퍼블릭 IP 주소를 확인해서 서버 접속 명령어를 실행하세요!

