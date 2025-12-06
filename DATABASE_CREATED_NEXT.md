# 데이터베이스 생성 완료! ✅

**상태:** 데이터베이스 `seoom` 생성 완료!

---

## ✅ 확인된 정보

- ✅ **데이터베이스 이름**: `seoom`
- ✅ **생성 상태**: 성공
- ✅ **다음 단계**: 사용자 만들기

---

## 🎯 다음 단계: 데이터베이스 사용자 만들기

MySQL 콘솔(`mysql>`)에서 다음 명령어를 입력하세요:

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

---

## 📋 나머지 단계

### 3단계: 권한 부여
```sql
GRANT ALL PRIVILEGES ON seoom.* TO 'seoom_user'@'localhost';
```

### 4단계: 권한 적용
```sql
FLUSH PRIVILEGES;
```

### 5단계: MySQL 나가기
```sql
EXIT;
```

---

## 💡 팁

- **비밀번호는 기억해두세요!** 나중에 `.env` 파일 설정할 때 필요해요!
- MySQL 명령어는 끝에 `;` (세미콜론)을 붙여야 해요
- 명령어는 **하나씩** 입력하세요

---

**다음 명령어를 입력하세요! (비밀번호를 원하는 것으로 변경하세요!)**

