# MySQL 보안 설정 - 익명 사용자 제거

**상황:** "Remove anonymous users?" 질문

---

## ✅ 답변: `Y` 입력

### 질문: "Remove anonymous users?"

**답변:** `Y` 입력 (대문자)

**이유:**
- 익명 사용자는 보안상 위험해요
- 제거하는 게 안전해요
- 프로덕션 환경에서는 필수예요

---

## 📋 현재 상황

MySQL이 `auth_socket` 인증을 사용하고 있어요:
- 비밀번호 없이도 root로 접속 가능해요
- 나중에 데이터베이스 만들 때는 문제없어요
- 지금은 익명 사용자만 제거하면 돼요

---

## 📋 나머지 질문들

### 질문 1: Remove anonymous users?
```
Remove anonymous users? (Press y|Y for Yes, any other key for No)
```
**답변:** `Y` 입력 ✅

### 질문 2: Disallow root login remotely?
```
Disallow root login remotely? (Press y|Y for Yes, any other key for No)
```
**답변:** `Y` 입력

### 질문 3: Remove test database?
```
Remove test database and access to it? (Press y|Y for Yes, any other key for No)
```
**답변:** `Y` 입력

### 질문 4: Reload privilege tables?
```
Reload privilege tables now? (Press y|Y for Yes, any other key for No)
```
**답변:** `Y` 입력

---

## 💡 요약

- **익명 사용자 제거:** `Y` 입력 (보안을 위해)
- **나머지 질문들:** 모두 `Y` 입력

---

**지금 `Y`를 입력하고 Enter 키를 누르세요!**

