# Laragon에서 Plans 데이터 Export하기

## 방법 1: 비밀번호 없이 시도

Laragon MySQL은 기본적으로 비밀번호가 없을 수 있어요:

```bash
mysqldump -u root seoom plans > plans_export.sql
```

## 방법 2: Laragon MySQL 경로 사용

Laragon의 MySQL 경로를 직접 사용:

```bash
C:\laragon\bin\mysql\mysql-8.0.30\bin\mysqldump.exe -u root seoom plans > plans_export.sql
```

(MySQL 버전에 따라 경로가 다를 수 있어요)

## 방법 3: HeidiSQL 또는 phpMyAdmin 사용

### HeidiSQL 사용:
1. HeidiSQL 실행
2. 로컬 MySQL 연결
3. `seoom` 데이터베이스 선택
4. `plans` 테이블 우클릭 → "Export grid as" → "SQL INSERT statements"
5. 파일로 저장

### phpMyAdmin 사용:
1. http://localhost/phpmyadmin 접속
2. `seoom` 데이터베이스 선택
3. `plans` 테이블 선택
4. "내보내기" 탭 클릭
5. "SQL" 형식 선택
6. "실행" 클릭하여 파일 다운로드

## 방법 4: MySQL Workbench 사용

1. MySQL Workbench 실행
2. 로컬 연결
3. `seoom` 데이터베이스 선택
4. `plans` 테이블 우클릭 → "Table Data Export Wizard"
5. 파일로 저장

## 방법 5: 직접 SQL 쿼리로 Export

Laragon MySQL 클라이언트에서:

```sql
USE seoom;

-- 결과를 파일로 저장하려면:
SELECT * FROM plans INTO OUTFILE 'C:/laragon/www/plans_export.csv'
FIELDS TERMINATED BY ',' 
ENCLOSED BY '"'
LINES TERMINATED BY '\n';
```

또는 INSERT 문 생성:

```sql
USE seoom;

SELECT CONCAT('INSERT INTO plans (id, name, slug, description, type, billing_type, price, one_time_price, traffic_limit_mb, features, limits, sort_order, is_active, is_default, created_at, updated_at, deleted_at) VALUES (',
    id, ', ',
    QUOTE(name), ', ',
    QUOTE(slug), ', ',
    IFNULL(QUOTE(description), 'NULL'), ', ',
    QUOTE(COALESCE(type, 'landing')), ', ',
    QUOTE(COALESCE(billing_type, 'free')), ', ',
    COALESCE(price, 0), ', ',
    IFNULL(one_time_price, 'NULL'), ', ',
    IFNULL(traffic_limit_mb, 'NULL'), ', ',
    QUOTE(COALESCE(features, '{}')), ', ',
    QUOTE(COALESCE(limits, '{}')), ', ',
    COALESCE(sort_order, 0), ', ',
    IF(is_active, 1, 0), ', ',
    IF(is_default, 1, 0), ', ',
    QUOTE(COALESCE(created_at, NOW())), ', ',
    QUOTE(COALESCE(updated_at, NOW())), ', ',
    IFNULL(QUOTE(deleted_at), 'NULL'),
    ');') AS sql_statement
FROM plans
ORDER BY id;
```

결과를 복사하여 `plans_import.sql` 파일로 저장하세요.

