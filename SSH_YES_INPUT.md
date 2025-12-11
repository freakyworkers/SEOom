# SSH 접속 - yes 입력 가이드

**상황:** SSH 접속 시 호스트 확인 메시지가 나왔어요

---

## ✅ 해결 방법

정확히 **`yes`**를 입력해야 해요!

### 현재 상황

```
Are you sure you want to continue connecting (yes/no/[fingerprint])?
```

이 메시지가 나왔을 때:
- ✅ **`yes`** 입력 (전체 단어)
- ❌ `y` 입력 (안 돼요!)
- ❌ `yse` 입력 (오타!)

---

## 📋 올바른 입력 방법

1. **`yes`** 입력 (소문자로)
2. **Enter 키** 누르기

---

## 🔄 다시 시도하는 방법

만약 잘못 입력했다면:

1. **`Ctrl + C`** 눌러서 취소
2. **다시 SSH 접속 명령어 실행:**
   ```powershell
   ssh -i "C:\Users\kangd\Desktop\세움배포파일\seoom-key.pem" ubuntu@52.79.104.130
   ```
3. **`yes`** 입력 (정확히!)
4. **Enter 키** 누르기

---

## ✅ 예상 결과

올바르게 입력하면:

```
Warning: Permanently added '52.79.104.130' (ED25519) to the list of known hosts.
Welcome to Ubuntu 22.04.3 LTS (GNU/Linux ...)

...

ubuntu@ip-xxx-xxx-xxx-xxx:~$
```

이제 서버에 접속된 거예요! 🎉

---

## 💡 팁

- `yes`는 전체 단어를 입력해야 해요
- 소문자로 입력하세요
- Enter 키를 누르면 됩니다

---

**다시 시도해보세요! `yes`를 정확히 입력하세요!**

