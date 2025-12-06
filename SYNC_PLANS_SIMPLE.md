# 로컬 Plans 데이터를 서버로 동기화하기 (간단 버전)

## 📋 방법 1: MySQL 직접 사용 (추천)

### 1단계: 로컬 MySQL에서 데이터 확인 및 Export

로컬 MySQL에 접속:

```sql
USE seoom;

-- Plans 데이터 확인
SELECT id, name, slug, type, billing_type, price, is_active FROM plans ORDER BY sort_order;

-- Export SQL 생성 (이 쿼리 결과를 복사)
SELECT CONCAT(
    'INSERT INTO plans (',
    'id, name, slug, description, type, billing_type, ',
    'price, one_time_price, traffic_limit_mb, ',
    'features, limits, sort_order, is_active, is_default, ',
    'created_at, updated_at, deleted_at',
    ') VALUES (',
    id, ', ',
    QUOTE(name), ', ',
    QUOTE(slug), ', ',
    IFNULL(QUOTE(description), 'NULL'), ', ',
    QUOTE(COALESCE(type, ''landing'')), ', ',
    QUOTE(COALESCE(billing_type, ''free'')), ', ',
    COALESCE(price, 0), ', ',
    IFNULL(one_time_price, 'NULL'), ', ',
    IFNULL(traffic_limit_mb, 'NULL'), ', ',
    QUOTE(COALESCE(features, ''{}''))), ', ',
    QUOTE(COALESCE(limits, ''{}''))), ', ',
    COALESCE(sort_order, 0), ', ',
    IF(is_active, 1, 0), ', ',
    IF(is_default, 1, 0), ', ',
    QUOTE(COALESCE(created_at, NOW()))), ', ',
    QUOTE(COALESCE(updated_at, NOW()))), ', ',
    IFNULL(QUOTE(deleted_at), 'NULL'),
    ');'
) AS sql_statement
FROM plans
ORDER BY id;
```

### 2단계: 결과를 파일로 저장

위 쿼리 결과의 모든 `sql_statement` 컬럼 값을 복사하여 `plans_import.sql` 파일로 저장하세요.

파일 시작 부분에 다음을 추가:

```sql
-- Plans 데이터 Import
-- 기존 데이터 삭제
SET FOREIGN_KEY_CHECKS=0;
DELETE FROM plans;
SET FOREIGN_KEY_CHECKS=1;

-- Plans 데이터 삽입
```

### 3단계: 서버에서 Import

서버에 SSH 접속 후:

```bash
cd /var/www/seoom
sudo mysql -u root seoom < plans_import.sql
```

또는 MySQL에 직접 접속:

```bash
sudo mysql -u root
```

```sql
USE seoom;

-- 기존 데이터 삭제
SET FOREIGN_KEY_CHECKS=0;
DELETE FROM plans;
SET FOREIGN_KEY_CHECKS=1;

-- 여기에 위에서 복사한 INSERT 문들을 붙여넣기
-- (복사한 모든 INSERT 문)

-- 확인
SELECT id, name, slug, type, billing_type, price, is_active FROM plans ORDER BY sort_order;
EXIT;
```

---

## 📋 방법 2: mysqldump 사용 (더 간단)

### 로컬에서:

```bash
mysqldump -u root -p seoom plans > plans_export.sql
```

### 서버로 복사:

```powershell
scp -i "C:\Users\kangd\Desktop\세움배포파일\seoom-key.pem" plans_export.sql ubuntu@54.180.2.108:/var/www/seoom/
```

### 서버에서:

```bash
cd /var/www/seoom
sudo mysql -u root seoom < plans_export.sql
```

---

## 📋 방법 3: Artisan Tinker 사용

### 로컬에서 (Laragon 터미널):

```bash
php artisan tinker
```

```php
// Plans 데이터를 JSON으로 export
$plans = \App\Models\Plan::all();
file_put_contents('plans_export.json', $plans->toJson(JSON_PRETTY_PRINT));
exit
```

### 서버에서:

```bash
cd /var/www/seoom
sudo php artisan tinker
```

```php
// 기존 데이터 삭제
\App\Models\Plan::truncate();

// JSON 파일 읽기
$plansData = json_decode(file_get_contents('plans_export.json'), true);

// 데이터 삽입
foreach ($plansData as $planData) {
    \App\Models\Plan::create($planData);
}
exit
```

---

## 💡 추천 방법

**방법 2 (mysqldump)**가 가장 간단하고 안전합니다!

