# 로컬 Plans 데이터를 서버로 동기화하기

## 📋 개요

로컬에서 설정한 요금제 관리(Plans) 데이터를 서버에 그대로 반영하는 가이드입니다.

## 🔄 동기화 방법

### 1단계: 로컬에서 Plans 데이터 Export

로컬 프로젝트 디렉토리에서:

```bash
php sync_plans_to_server.php export
```

이 명령어는 `plans_export.sql` 파일을 생성합니다.

### 2단계: SQL 파일을 서버로 복사

생성된 `plans_export.sql` 파일을 서버로 복사하세요.

**방법 1: SCP 사용 (Windows PowerShell)**
```powershell
scp -i "C:\Users\kangd\Desktop\세움배포파일\seoom-key.pem" plans_export.sql ubuntu@52.79.104.130:/var/www/seoom/
```

**방법 2: WinSCP 사용**
- WinSCP로 서버에 연결
- `plans_export.sql` 파일을 `/var/www/seoom/` 디렉토리로 업로드

### 3단계: 서버에서 Plans 데이터 Import

서버에 SSH 접속 후:

```bash
cd /var/www/seoom
sudo php sync_plans_to_server.php import
```

### 4단계: 확인

서버에서 MySQL로 접속하여 확인:

```bash
sudo mysql -u root
```

```sql
USE seoom;
SELECT id, name, slug, type, billing_type, price, is_active FROM plans ORDER BY sort_order;
EXIT;
```

---

## ⚠️ 주의사항

1. **기존 데이터 삭제**: Import 시 기존 plans 데이터가 모두 삭제되고 로컬 데이터로 교체됩니다.
2. **외래 키 제약**: Import 중 외래 키 제약 조건이 일시적으로 비활성화됩니다.
3. **백업 권장**: 서버의 기존 plans 데이터를 백업하고 싶다면 먼저 export하세요.

---

## 🔄 서버 → 로컬 동기화 (역방향)

서버의 데이터를 로컬로 가져오려면:

1. 서버에서 export:
   ```bash
   cd /var/www/seoom
   sudo php sync_plans_to_server.php export
   ```

2. 로컬로 파일 복사 (SCP):
   ```powershell
   scp -i "C:\Users\kangd\Desktop\세움배포파일\seoom-key.pem" ubuntu@52.79.104.130:/var/www/seoom/plans_export.sql .
   ```

3. 로컬에서 import:
   ```bash
   php sync_plans_to_server.php import
   ```

---

## 📝 전체 명령어 요약

### 로컬 → 서버

```bash
# 로컬
php sync_plans_to_server.php export

# 파일 복사 (PowerShell)
scp -i "C:\Users\kangd\Desktop\세움배포파일\seoom-key.pem" plans_export.sql ubuntu@52.79.104.130:/var/www/seoom/

# 서버
cd /var/www/seoom
sudo php sync_plans_to_server.php import
```

---

## 🐛 문제 해결

### "plans 테이블이 존재하지 않습니다" 오류
- 마이그레이션이 실행되지 않았을 수 있습니다.
- 서버에서 `php artisan migrate` 실행

### "파일을 찾을 수 없습니다" 오류
- `plans_export.sql` 파일이 현재 디렉토리에 있는지 확인
- 파일 경로를 확인하세요

### Import 실패
- MySQL 권한 확인
- 외래 키 제약 조건 확인
- SQL 파일의 문법 확인

