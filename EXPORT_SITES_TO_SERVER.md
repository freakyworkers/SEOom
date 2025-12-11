# 로컬 사이트 데이터를 서버로 이전하기

## 📋 Export할 사이트
- `test-site`
- `e`

## 방법 1: mysqldump 사용 (추천)

### 1단계: 로컬에서 사이트 ID 확인

Laragon MySQL 클라이언트 또는 HeidiSQL에서:

```sql
USE seoom;

-- 사이트 ID 확인
SELECT id, name, slug FROM sites WHERE slug IN ('test-site', 'e') AND deleted_at IS NULL;
```

예를 들어 결과가:
- test-site: id = 5
- e: id = 13

이면 다음 단계에서 이 ID들을 사용합니다.

### 2단계: mysqldump로 데이터 Export

Laragon 터미널에서:

```bash
# 사이트 기본 정보
mysqldump -u root seoom sites --where="id IN (5, 13)" --no-create-info > sites_export.sql

# 사용자 데이터
mysqldump -u root seoom users --where="site_id IN (5, 13)" --no-create-info >> sites_export.sql

# 게시판 데이터
mysqldump -u root seoom boards --where="site_id IN (5, 13)" --no-create-info >> sites_export.sql

# 게시글 데이터
mysqldump -u root seoom posts --where="site_id IN (5, 13)" --no-create-info >> sites_export.sql

# 댓글 데이터
mysqldump -u root seoom comments --where="site_id IN (5, 13)" --no-create-info >> sites_export.sql

# 사이트 설정
mysqldump -u root seoom site_settings --where="site_id IN (5, 13)" --no-create-info >> sites_export.sql
```

### 3단계: 파일 시작 부분 수정

생성된 `sites_export.sql` 파일의 맨 앞에 다음을 추가:

```sql
-- 사이트 데이터 Import
-- 생성일: 2025-12-06
-- 사이트: test-site, e

SET FOREIGN_KEY_CHECKS=0;

-- 기존 데이터 삭제 (중복 방지)
DELETE FROM sites WHERE id IN (5, 13);
DELETE FROM users WHERE site_id IN (5, 13);
DELETE FROM boards WHERE site_id IN (5, 13);
DELETE FROM posts WHERE site_id IN (5, 13);
DELETE FROM comments WHERE site_id IN (5, 13);
DELETE FROM site_settings WHERE site_id IN (5, 13);

-- 데이터 삽입
```

파일 끝에 추가:

```sql
SET FOREIGN_KEY_CHECKS=1;
```

## 방법 2: HeidiSQL 사용

1. HeidiSQL 실행
2. 로컬 MySQL 연결
3. `seoom` 데이터베이스 선택
4. 각 테이블에서:
   - 테이블 우클릭 → "Filter data"
   - `site_id IN (5, 13)` 조건 입력
   - "Export grid as" → "SQL INSERT statements"
   - 파일로 저장

## 방법 3: 전체 스크립트 (한 번에)

Laragon 터미널에서:

```bash
# 사이트 ID를 먼저 확인한 후 아래 스크립트 수정
SITE_IDS="5,13"

mysqldump -u root seoom \
  --where="id IN ($SITE_IDS)" sites \
  --where="site_id IN ($SITE_IDS)" users boards posts comments site_settings \
  --no-create-info \
  > sites_export.sql
```

## 4단계: 서버로 파일 전송

WinSCP 또는 scp 사용:

```bash
scp -i "C:\Users\kangd\Desktop\세움배포파일\seoom-key.pem" sites_export.sql ubuntu@52.79.104.130:~/
```

## 5단계: 서버에서 Import

서버에 SSH 접속 후:

```bash
cd /var/www/seoom
sudo mysql -u seoom_user -p seoom < ~/sites_export.sql
```

비밀번호: `Tpdk1021!`

## ⚠️ 주의사항

1. **ID 충돌**: 서버에 이미 같은 ID의 사이트가 있으면 충돌이 발생할 수 있습니다.
   - 해결: SQL 파일에서 ID를 새로운 값으로 변경하거나, 서버에서 기존 데이터를 먼저 확인하세요.

2. **파일 업로드**: 게시글에 첨부된 파일들은 별도로 복사해야 합니다.
   - 로컬: `storage/app/public/sites/{site_id}/`
   - 서버: `/var/www/seoom/storage/app/public/sites/{site_id}/`

3. **사용자 비밀번호**: 사용자 비밀번호는 해시된 상태로 저장되므로 그대로 이전됩니다.

4. **외래 키**: `SET FOREIGN_KEY_CHECKS=0`으로 비활성화했으므로 순서는 중요하지 않습니다.

