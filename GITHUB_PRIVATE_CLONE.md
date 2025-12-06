# GitHub Private 저장소 클론 가이드

**상황:** 저장소가 private이어서 인증이 필요해요

---

## 🎯 해결 방법: Personal Access Token 사용

### 1단계: 현재 작업 취소

터미널에서:
- **`Ctrl + C`** 눌러서 취소

### 2단계: GitHub에서 Personal Access Token 생성

1. **GitHub 웹사이트 접속**
   - https://github.com 로그인

2. **Settings로 이동**
   - 오른쪽 위 프로필 클릭 → **"Settings"**

3. **Developer settings로 이동**
   - 왼쪽 메뉴 맨 아래 **"Developer settings"** 클릭

4. **Personal access tokens로 이동**
   - **"Personal access tokens"** → **"Tokens (classic)"** 클릭

5. **토큰 생성**
   - **"Generate new token"** → **"Generate new token (classic)"** 클릭
   - **Note**: `SEOom Deployment` 입력 (설명)
   - **Expiration**: 원하는 기간 선택 (예: 90 days)
   - **Select scopes**: 
     - ✅ **`repo`** 체크 (모든 권한)
     - 또는 ✅ **`read:packages`** 체크 (읽기 권한만)
   - **"Generate token"** 클릭

6. **토큰 복사**
   - ⚠️ **중요:** 토큰을 복사해두세요! 다시 볼 수 없어요!
   - 예: `ghp_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx`

### 3단계: 토큰으로 클론

서버 터미널에서:

```bash
sudo git clone https://토큰@github.com/freakyworkers/SEOom.git seoom
```

**예시:**
```bash
sudo git clone https://ghp_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx@github.com/freakyworkers/SEOom.git seoom
```

Enter 키 누르면 클론이 시작돼요!

---

## 📋 전체 과정

### 1. 취소
```
Ctrl + C
```

### 2. GitHub에서 토큰 생성
- Settings → Developer settings → Personal access tokens → Generate new token
- `repo` 권한 체크
- 토큰 복사

### 3. 토큰으로 클론
```bash
sudo git clone https://토큰@github.com/freakyworkers/SEOom.git seoom
```

### 4. 소유권 변경
```bash
sudo chown -R www-data:www-data /var/www/seoom
sudo chmod -R 755 /var/www/seoom
```

---

## 💡 팁

- **토큰은 안전하게 보관하세요!**
- 토큰이 만료되면 새로 생성해야 해요
- 토큰은 URL에 포함되지만, 이건 일회성 사용이에요

---

## 🆘 문제 해결

### 문제: "Authentication failed" 오류

**해결 방법:**
- 토큰이 올바른지 확인
- 토큰에 `repo` 권한이 있는지 확인
- 토큰이 만료되지 않았는지 확인

### 문제: 토큰을 잃어버렸어요

**해결 방법:**
- GitHub에서 새 토큰 생성
- 기존 토큰은 삭제할 수 있어요

---

## ✅ 완료 확인

클론이 완료되면:

```bash
ls -la /var/www/seoom
```

Enter 키 누르면 프로젝트 파일들이 보여요.

---

**먼저 `Ctrl + C`를 눌러서 취소하고, GitHub에서 토큰을 생성하세요!**

