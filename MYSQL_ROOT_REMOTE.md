# MySQL 보안 설정 - 원격 root 로그인 차단

**상황:** "Disallow root login remotely?" 질문

---

## ✅ 답변: `Y` 입력

### 질문: "Disallow root login remotely?"

**답변:** `Y` 입력 (대문자)

**이유:**
- 원격에서 root로 로그인하는 것은 보안상 위험해요
- 로컬에서만 root로 접속할 수 있게 하는 게 안전해요
- 프로덕션 환경에서는 필수예요

---

## 📋 나머지 질문들

### 질문 1: Disallow root login remotely?
```
Disallow root login remotely? (Press y|Y for Yes, any other key for No)
```
**답변:** `Y` 입력 ✅

### 질문 2: Remove test database?
```
Remove test database and access to it? (Press y|Y for Yes, any other key for No)
```
**답변:** `Y` 입력

### 질문 3: Reload privilege tables?
```
Reload privilege tables now? (Press y|Y for Yes, any other key for No)
```
**답변:** `Y` 입력

---

## 💡 요약

- **원격 root 로그인 차단:** `Y` 입력 (보안을 위해)
- **나머지 질문들:** 모두 `Y` 입력

---

**지금 `Y`를 입력하고 Enter 키를 누르세요!**

