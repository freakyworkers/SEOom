# MySQL 데이터베이스 만들기

**상황:** MySQL 콘솔에 접속됨 (`mysql>`)

---

## 🎯 다음 단계: 데이터베이스 및 사용자 만들기

MySQL 콘솔(`mysql>`)에서 다음 명령어를 **하나씩** 입력하세요:

### 1단계: 데이터베이스 만들기

```sql
CREATE DATABASE seoom CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Enter 키 누르기

**예상 결과:**
```
Query OK, 1 row affected (0.00 sec)
```

### 2단계: 데이터베이스 사용자 만들기

```sql
CREATE USER 'seoom_user'@'localhost' IDENTIFIED BY 'MyPassword123!';
```

Enter 키 누르기

**⚠️ 중요:** `MyPassword123!` 부분을 원하는 비밀번호로 변경하세요!
- 예: `'seoom_user'@'localhost' IDENTIFIED BY 'seoom2025!';`
- **비밀번호는 기억해두세요!** 나중에 `.env` 파일 설정할 때 필요해요!

**예상 결과:**
```
Query OK, 0 rows affected (0.00 sec)
```

### 3단계: 권한 부여

```sql
GRANT ALL PRIVILEGES ON seoom.* TO 'seoom_user'@'localhost';
```

Enter 키 누르기

**예상 결과:**
```
Query OK, 0 rows affected (0.00 sec)
```

### 4단계: 권한 적용

```sql
FLUSH PRIVILEGES;
```

Enter 키 누르기

**예상 결과:**
```
Query OK, 0 rows affected (0.00 sec)
```

### 5단계: MySQL 나가기

```sql
EXIT;
```

Enter 키 누르면 서버 프롬프트로 돌아가요.

---

## 📋 전체 명령어 (복사해서 사용)

```sql
CREATE DATABASE seoom CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'seoom_user'@'localhost' IDENTIFIED BY 'MyPassword123!';
GRANT ALL PRIVILEGES ON seoom.* TO 'seoom_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

**⚠️ 중요:** `MyPassword123!` 부분을 원하는 비밀번호로 변경하세요!

---

## 💡 팁

- **비밀번호는 기억해두세요!** 나중에 `.env` 파일 설정할 때 필요해요!
- MySQL 명령어는 끝에 `;` (세미콜론)을 붙여야 해요
- 명령어는 **하나씩** 입력하세요
- `EXIT;`로 MySQL 콘솔을 나갈 수 있어요

---

## ✅ 완료 확인

`EXIT;` 후 서버 프롬프트(`ubuntu@ip-172-31-38-145:~$`)로 돌아오면 완료!

---

## 🎯 다음 단계 (데이터베이스 만들기 완료 후)

1. **Apache 설치** (6-7 단계)
2. **Git 설치** (6-8 단계)
3. **프로젝트 업로드** (6-9 단계)
4. **Laravel 설정** (6-10 단계)

---

**첫 번째 명령어를 입력하세요!**

