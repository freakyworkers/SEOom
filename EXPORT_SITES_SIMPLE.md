# 로컬 사이트 데이터를 서버로 이전하기 (간단 버전)

## 📋 Export할 사이트
- `test-site`
- `e`

## 🚀 빠른 시작

### 1단계: 사이트 ID 확인

**HeidiSQL** 또는 **Laragon MySQL 클라이언트**에서:

```sql
USE seoom;

SELECT id, name, slug FROM sites 
WHERE slug IN ('test-site', 'e') 
AND deleted_at IS NULL;
```

예를 들어 결과가:
```
id | name        | slug
---|-------------|----------
5  | 테스트 사이트 | test-site
13 | e 사이트     | e
```

이면 **사이트 ID는 5와 13**입니다.

### 2단계: mysqldump로 Export

**Laragon 터미널**에서 (또는 `export_sites_simple.bat` 실행):

```bash
# 사이트 ID를 위에서 확인한 값으로 변경하세요
SITE_IDS="5,13"

# 파일 초기화
echo -- 사이트 데이터 Export > sites_export.sql
echo SET FOREIGN_KEY_CHECKS=0; >> sites_export.sql
echo. >> sites_export.sql

# 각 테이블 Export
mysqldump -u root seoom sites --where="id IN ($SITE_IDS)" --no-create-info >> sites_export.sql
mysqldump -u root seoom users --where="site_id IN ($SITE_IDS)" --no-create-info >> sites_export.sql
mysqldump -u root seoom boards --where="site_id IN ($SITE_IDS)" --no-create-info >> sites_export.sql
mysqldump -u root seoom posts --where="site_id IN ($SITE_IDS)" --no-create-info >> sites_export.sql
mysqldump -u root seoom comments --where="site_id IN ($SITE_IDS)" --no-create-info >> sites_export.sql
mysqldump -u root seoom site_settings --where="site_id IN ($SITE_IDS)" --no-create-info >> sites_export.sql

# 파일 끝
echo. >> sites_export.sql
echo SET FOREIGN_KEY_CHECKS=1; >> sites_export.sql
```

또는 **Windows에서**:

```cmd
REM export_sites_simple.bat 파일을 열어서 SITE_IDS를 수정한 후 실행
```

### 3단계: 서버로 파일 전송

**WinSCP** 또는 **scp** 사용:

```bash
scp -i "C:\Users\kangd\Desktop\세움배포파일\seoom-key.pem" sites_export.sql ubuntu@52.79.104.130:~/
```

### 4단계: 서버에서 Import

서버에 SSH 접속 후:

```bash
cd /var/www/seoom
sudo mysql -u seoom_user -p seoom < ~/sites_export.sql
```

비밀번호: `Tpdk1021!`

## ⚠️ 주의사항

1. **ID 충돌**: 서버에 이미 같은 ID가 있으면 충돌이 발생할 수 있습니다.
   - 해결: 서버에서 기존 사이트 ID를 확인하고, 필요시 SQL 파일의 ID를 변경하세요.

2. **파일 업로드**: 게시글 첨부 파일은 별도로 복사해야 합니다.
   - 로컬: `C:\laragon\www\SEOom\storage\app\public\sites\{site_id}\`
   - 서버: `/var/www/seoom/storage/app/public/sites/{site_id}/`

3. **추가 테이블**: 필요시 다음 테이블도 추가로 Export하세요:
   - `subscriptions`
   - `notifications`
   - `messages`
   - `saved_posts`
   - `post_likes`
   - `post_attachments`
   - 기타 사이트 관련 테이블

## 🔍 서버에서 기존 사이트 ID 확인

서버에서 다음 명령어로 기존 사이트 ID를 확인:

```bash
sudo mysql -u seoom_user -p seoom -e "SELECT id, name, slug FROM sites ORDER BY id;"
```

충돌을 피하려면 SQL 파일에서 ID를 새로운 값으로 변경하세요.

