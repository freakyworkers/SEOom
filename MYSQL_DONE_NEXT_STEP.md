# MySQL 보안 설정 완료! ✅

**상태:** MySQL 설치 및 보안 설정 완료!

---

## ✅ 완료된 작업

- ✅ MySQL 설치 완료
- ✅ MySQL 보안 설정 완료
- ✅ 다음 단계 준비 완료

---

## 🎯 다음 단계: 데이터베이스 만들기 (6-6 단계)

Laravel 프로젝트를 위한 데이터베이스를 만들어야 해요!

### 1단계: MySQL 접속

```bash
sudo mysql -u root
```

Enter 키 누르면 MySQL 콘솔로 들어가요.

**예상 결과:**
```
Welcome to the MySQL monitor.  Commands end with ; or \g.
mysql>
```

### 2단계: 데이터베이스 만들기

MySQL 콘솔에서 다음 명령어를 입력하세요:

```sql
CREATE DATABASE seoom CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Enter 키 누르기

**예상 결과:**
```
Query OK, 1 row affected (0.00 sec)
```

### 3단계: 데이터베이스 사용자 만들기

```sql
CREATE USER 'seoom_user'@'localhost' IDENTIFIED BY '강력한비밀번호입력';
```

Enter 키 누르기

**⚠️ 중요:** `강력한비밀번호입력` 부분을 실제 비밀번호로 변경하세요!
- 예: `'seoom_user'@'localhost' IDENTIFIED BY 'MyPassword123!';`

**예상 결과:**
```
Query OK, 0 rows affected (0.00 sec)
```

### 4단계: 권한 부여

```sql
GRANT ALL PRIVILEGES ON seoom.* TO 'seoom_user'@'localhost';
```

Enter 키 누르기

**예상 결과:**
```
Query OK, 0 rows affected (0.00 sec)
```

### 5단계: 권한 적용

```sql
FLUSH PRIVILEGES;
```

Enter 키 누르기

**예상 결과:**
```
Query OK, 0 rows affected (0.00 sec)
```

### 6단계: MySQL 나가기

```sql
EXIT;
```

Enter 키 누르면 서버 프롬프트로 돌아가요.

---

## 📋 전체 명령어 (MySQL 콘솔에서)

MySQL 콘솔(`mysql>`)에 들어간 후:

```sql
CREATE DATABASE seoom CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'seoom_user'@'localhost' IDENTIFIED BY '강력한비밀번호입력';
GRANT ALL PRIVILEGES ON seoom.* TO 'seoom_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

**⚠️ 중요:** `강력한비밀번호입력` 부분을 실제 비밀번호로 변경하세요!

---

## 💡 팁

- **비밀번호는 기억해두세요!** 나중에 `.env` 파일 설정할 때 필요해요!
- MySQL 명령어는 끝에 `;` (세미콜론)을 붙여야 해요
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

**MySQL 접속 명령어를 실행하세요!**

